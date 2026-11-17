<?php

namespace App\Services\PublicOrders;

use App\Models\Customer;
use App\Models\CustomerOrderAccess;
use App\Models\CustomerOrderSession;
use App\Models\DigitalMenu;
use App\Models\RoomTable;
use App\Services\Orders\TableOrderService;
use App\Services\PhoneNumberService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerAccessService
{
    public function request(DigitalMenu $menu, string $language, array $input, ?string $tableToken = null): void
    {
        abort_unless($menu->is_visible && $menu->is_published && in_array($language, $menu->enabled_languages, true), 404);
        Validator::make($input, ['name' => 'required|string|max:150', 'email' => 'required|email|max:255', 'phone' => 'required|string|max:40', 'privacy' => 'accepted'])->validate();
        try {
            $phone = app(PhoneNumberService::class)->normalize($input['phone']);
        } catch (\InvalidArgumentException) {
            throw ValidationException::withMessages(['phone' => __('Numero di telefono non valido.')]);
        }
        $email = mb_strtolower(trim($input['email']));
        $key = 'order-access:'.hash('sha256', request()->ip().'|'.$email);
        if (RateLimiter::tooManyAttempts($key, 3) || RateLimiter::tooManyAttempts('order-access-ip:'.request()->ip(), 20)) {
            throw ValidationException::withMessages(['email' => __('Attendi qualche minuto prima di richiedere un altro link.')]);
        }
        RateLimiter::hit($key, 600);
        RateLimiter::hit('order-access-ip:'.request()->ip(), 600);
        $table = $tableToken ? RoomTable::where('ordering_token', $tableToken)->whereHas('room', fn ($q) => $q->where('active', true))->firstOrFail() : null;
        if (! $table) {
            abort_unless(app(OrderingSettings::class)->get()['enabled'], 404);
        }
        $token = Str::random(64);
        DB::transaction(function () use ($menu, $language, $input, $phone, $email, $table, $token) {
            $access = CustomerOrderAccess::create(['order_id' => $table ? DB::table('order_tables')->where('active_table_id', $table->id)->value('order_id') : null, 'token_hash' => hash('sha256', $token), 'identity' => ['name' => trim($input['name']), 'email' => $email, 'phone' => $phone, 'ip' => request()->ip()], 'menu_id' => $menu->id, 'room_table_id' => $table?->id, 'table_token' => $table?->ordering_token, 'language' => $language, 'expires_at' => now()->addMinutes(20)]);
            app(OrderNotifications::class)->record('access:'.$access->id, $access->id, $email, $input['name'], __('Accedi al menu per ordinare'), __('Conferma il tuo indirizzo email per iniziare l’ordine. Il link è valido per 20 minuti.'), route('public-orders.verify', ['token' => $token]), $language);
        });
    }

    public function redeem(string $token): string
    {
        return DB::transaction(function () use ($token) {
            $access = CustomerOrderAccess::where('token_hash', hash('sha256', $token))->lockForUpdate()->firstOrFail();
            app()->setLocale($access->language);
            abort_if($access->consumed_at || $access->expires_at->isPast(), 410, __('Link scaduto o già utilizzato.'));
            $menu = DigitalMenu::findOrFail($access->menu_id);
            abort_unless($menu->is_visible && $menu->is_published && in_array($access->language, $menu->enabled_languages, true), 404);
            $identity = $access->identity;
            $byEmail = Customer::where('email', $identity['email'])->lockForUpdate()->first();
            $byPhone = Customer::where('phone', $identity['phone'])->lockForUpdate()->first();
            if ($byPhone && (! $byEmail || $byPhone->id !== $byEmail->id)) {
                throw ValidationException::withMessages(['identity' => __('Il telefono risulta associato a un altro profilo. Contatta il ristorante per verificare i recapiti.')]);
            }
            if ($byEmail && $byEmail->phone && $byEmail->phone !== $identity['phone']) {
                throw ValidationException::withMessages(['identity' => __('I recapiti non corrispondono al profilo esistente. Contatta il ristorante.')]);
            }
            $customer = $byEmail ?? Customer::create(['firstname' => $identity['name'], 'display_name' => $identity['name'], 'email' => $identity['email'], 'phone' => $identity['phone'], 'registration_source' => 'customer_order', 'lang' => $access->language, 'consent_privacy' => true, 'datetime_consent_privacy' => now(), 'ip_consent_privacy' => $identity['ip']]);
            abort_if($customer->blacklisted, 403);
            if (! $customer->consent_privacy) {
                $customer->update(['consent_privacy' => true, 'datetime_consent_privacy' => now(), 'ip_consent_privacy' => $identity['ip']]);
            }
            if (! $customer->phone) {
                $customer->update(['phone' => $identity['phone']]);
            }
            $order = null;
            $table = null;
            if ($access->room_table_id) {
                $table = RoomTable::with('room')->whereKey($access->room_table_id)->where('ordering_token', $access->table_token)->firstOrFail();
                $order = app(TableOrderService::class)->open($table->id, max(1, $table->min_people), null, false);
                abort_if($access->order_id && $order->id !== $access->order_id, 410, __('Il conto del tavolo è cambiato. Scansiona nuovamente il QR e richiedi un nuovo link.'));
            } else {
                abort_unless(app(OrderingSettings::class)->get()['enabled'], 404);
            }
            $session = CustomerOrderSession::create(['customer_id' => $customer->id, 'order_id' => $order?->id, 'room_table_id' => $table?->id, 'table_token' => $table?->ordering_token, 'expires_at' => now()->addHours(6)]);
            $access->update(['consumed_at' => now()]);
            request()->session()->regenerate();
            session()->put('customer_order_session', $session->id);
            // Laravel EncryptCookies encrypts and authenticates this recognition-only cookie.
            Cookie::queue(cookie('ordering_recognition', $customer->phone, 60 * 24 * 30, null, null, request()->isSecure(), true, false, 'lax'));

            return route('public-orders.menu', ['language' => $access->language, 'menu' => $menu, 'table' => $table?->ordering_token]);
        }, 3);
    }

    public function current(?string $tableToken = null): ?CustomerOrderSession
    {
        $session = CustomerOrderSession::with(['customer', 'order', 'table.room'])->find(session('customer_order_session'));
        if (! $session || $session->expires_at->isPast() || $session->customer->blacklisted) {
            return null;
        }
        if ($tableToken === null) {
            return $session->room_table_id === null ? $session : null;
        }
        if (! $session->table || ! $session->table->room->active || ! hash_equals($session->table_token ?? '', $tableToken) || ! hash_equals($session->table->ordering_token ?? '', $tableToken) || $session->order?->status !== 'open') {
            return null;
        }

        return $session;
    }

    public function recognizedName(): ?string
    {
        $phone = request()->cookie('ordering_recognition');

        return $phone ? Customer::where('phone', $phone)->value('firstname') : null;
    }
}

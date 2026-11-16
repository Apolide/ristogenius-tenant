<?php

namespace App\Services\PublicOrders;

use App\Models\CustomerOrderSession;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\RoomTable;
use App\Services\Orders\OrderPricingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function quote(array $cart, string $type, string $language): array
    {
        Validator::make(['cart' => $cart], ['cart' => 'required|array|min:1|max:100'])->validate();
        $quotes = [];
        $subtotal = 0;
        foreach ($cart as $line) {
            try {
                $quote = app(OrderPricingService::class)->quote($line, true, $type, $language);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
                throw ValidationException::withMessages(['cart' => 'Un prodotto non è più disponibile. Rimuovilo dal carrello.']);
            }
            $quotes[] = $quote;
            $subtotal += $quote['attributes']['quantity'] * $quote['attributes']['unit_price_cents'];
        }

        return ['lines' => $quotes, 'subtotal' => $subtotal];
    }

    public function submit(array $cart, string $language, ?string $tableToken, array $details, string $submissionKey, int $expectedTotal): OrderCourse
    {
        $access = app(CustomerAccessService::class)->current($tableToken);
        abort_unless($access, 403, 'Verifica la tua email prima di ordinare.');
        Validator::make($details, ['type' => 'required|in:dine_in,pickup,delivery', 'notes' => 'nullable|string|max:2000', 'address' => 'nullable|string|max:500', 'date' => 'nullable|date_format:Y-m-d', 'time' => 'nullable|date_format:H:i', 'neighborhood' => 'nullable|integer'])->validate();
        abort_unless(($tableToken && $details['type'] === 'dine_in') || (! $tableToken && in_array($details['type'], ['pickup', 'delivery'])), 403);

        return DB::transaction(function () use ($access, $cart, $language, $tableToken, $details, $submissionKey, $expectedTotal) {
            $session = CustomerOrderSession::lockForUpdate()->findOrFail($access->id);
            abort_if($session->expires_at->isPast(), 403);
            $existing = OrderCourse::where('submission_key', $submissionKey)->first();
            if ($existing) {
                abort_unless($existing->customer_id === $access->customer_id, 403);

                return $existing;
            }
            $quote = $this->quote($cart, $details['type'], $language);
            $fee = 0;
            $percentage = 0;
            $fulfillment = [];
            if ($tableToken) {
                $order = Order::lockForUpdate()->findOrFail($access->order_id);
                $this->check($order->cash_snapshot === null && ! $order->payments()->exists(), 'Conto in fase di incasso: non è possibile aggiungere ordini.');
                $this->check($order->status === 'open', 'Questo conto è stato chiuso. Scansiona nuovamente il QR.');
                $table = RoomTable::with('room')->whereKey($access->room_table_id)->where('ordering_token', $tableToken)->firstOrFail();
                $this->check($table->room->active, 'Sala non disponibile.');
                $percentage = \App\Services\Orders\OrderService::cents((string) $table->room->service_charge_percentage);
                $fee = intdiv($quote['subtotal'] * $percentage + 5000, 10000);
            } else {
                $settings = app(OrderingSettings::class)->get();
                $this->check($settings['cash_enabled'], 'Gli ordini in contanti non sono disponibili.');
                $fulfillment = app(AvailabilityService::class)->validate($details['date'] ?? '', $details['time'] ?? '', $details['type'], $quote['subtotal'], $details['neighborhood'] ?? null);
                if ($details['type'] === 'delivery') {
                    $this->check(trim($details['address'] ?? '') !== '', 'Indica l’indirizzo di consegna.');
                }
                $fee = $fulfillment['delivery_fee_cents'];
            }
            $this->check($expectedTotal === $quote['subtotal'] + $fee, 'Il totale è cambiato. Controlla il riepilogo e conferma nuovamente.');
            if (! $tableToken) {
                $customer = $access->customer;
                $order = Order::create($fulfillment + [
                    'source' => 'customer', 'fulfillment' => $details['type'], 'fulfillment_status' => 'received', 'service_date' => $details['date'],
                    'reference' => 'OC'.now()->format('ymd').'-'.strtoupper(Str::random(8)),
                    'customer_id' => $customer->id, 'customer_name' => $customer->display_name ?: $customer->firstname, 'customer_email' => $customer->email, 'customer_phone' => $customer->phone,
                    'covers' => 0, 'cover_price_cents' => 0, 'payment_method' => 'cash', 'delivery_address' => $details['type'] === 'delivery' ? trim($details['address']) : null, 'customer_notes' => $details['notes'] ?? '',
                ]);
            }
            $course = $order->courses()->create(['number' => (int) $order->courses()->max('number') + 1, 'customer_id' => $access->customer_id, 'customer_name' => $access->customer->display_name ?: $access->customer->firstname, 'customer_notes' => $details['notes'] ?? '', 'submission_key' => $submissionKey, 'service_percentage_bps' => $percentage]);
            foreach ($quote['lines'] as $line) {
                app(OrderPricingService::class)->append($course, $line);
            }
            $order->events()->create(['action' => 'customer_submitted', 'details' => ['course' => $course->number, 'customer' => $course->customer_name]]);
            $notifications = app(OrderNotifications::class);
            if (! $tableToken) {
                $notifications->status($order, 'received');
                $notifications->incoming($order);
            } else {
                $notifications->incomingCourse($course);
                $notifications->record('table:'.$course->id, $order->id, $access->customer->email, $course->customer_name, 'Ordine al tavolo ricevuto', 'La tua richiesta è stata ricevuta dal ristorante.');
            }

            return $course;
        }, 3);
    }

    private function check(bool $ok, string $message): void
    {
        if (! $ok) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}

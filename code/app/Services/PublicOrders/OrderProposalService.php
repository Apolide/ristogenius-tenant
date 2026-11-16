<?php

namespace App\Services\PublicOrders;

use App\Models\CustomerOrderProposal;
use App\Models\Department;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\Order;
use App\Services\Orders\OrderPricingService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderProposalService
{
    public function assertEditable(Order $order): void
    {
        $this->check(in_array($order->fulfillment, ['pickup', 'delivery']) && $order->status === 'open'
            && in_array($order->fulfillment_status, ['received', 'accepted']) && ! $order->payments()->exists()
            && ! $order->courses()->whereNotNull('sent_at')->exists(),
            'Le proposte sono disponibili soltanto per ordini non pagati e non ancora in preparazione.');
    }

    public function initialLines(Order $order): array
    {
        return $order->items()->with('modifiers')->get()->map(fn ($item) => [
            'original_item_id' => $item->id, 'product_id' => $item->product_id, 'category_id' => null,
            'quantity' => (int) $item->quantity, 'notes' => $item->notes ?? '',
            'added' => $item->modifiers->where('action', 'add')->whereNotNull('ingredient_id')->pluck('ingredient_id')->all(),
            'removed' => $item->modifiers->where('action', 'remove')->whereNotNull('ingredient_id')->pluck('ingredient_id')->all(),
        ])->all();
    }

    public function priceLines(Order $order, array $lines): array
    {
        Validator::make(['lines' => $lines], [
            'lines' => 'required|array|min:1|max:100', 'lines.*.original_item_id' => 'nullable|uuid|distinct',
            'lines.*.product_id' => 'nullable|uuid', 'lines.*.quantity' => 'required|integer|min:1|max:100',
            'lines.*.notes' => 'nullable|string|max:2000', 'lines.*.added' => 'array|max:100', 'lines.*.removed' => 'array|max:100',
            'lines.*.added.*' => 'uuid|distinct', 'lines.*.removed.*' => 'uuid|distinct',
        ])->validate();
        $quotes = [];
        $subtotal = 0;
        foreach ($lines as $line) {
            $item = empty($line['original_item_id']) ? null : $order->items()->with('modifiers')->findOrFail($line['original_item_id']);
            if ($item) {
                $this->check(($line['product_id'] ?? null) === $item->product_id, 'Per sostituire un prodotto rimuovi la riga e aggiungi il nuovo prodotto.');
                $attributes = $item->only(['product_id', 'menu_id', 'product_name', 'menu_name', 'department_id', 'department_name', 'requires_production', 'base_price_cents', 'vat']);
                if ($item->product_id) {
                    $product = DigitalMenuProduct::with('ingredients')->findOrFail($item->product_id);
                    [$price,$modifiers] = app(OrderPricingService::class)->modifiers($product, $item->base_price_cents, $line['added'] ?? [], $line['removed'] ?? [], $item);
                } else {
                    $this->check(empty($line['added']) && empty($line['removed']), 'Il prodotto è stato rimosso dal catalogo: sono modificabili soltanto quantità e note.');
                    $price = $item->unit_price_cents;
                    $modifiers = $item->modifiers->map(fn ($m) => $m->only(['ingredient_id', 'ingredient_name', 'action', 'price_cents']))->all();
                }
                $quote = ['attributes' => $attributes + ['quantity' => (int) $line['quantity'], 'notes' => $line['notes'] ?? '', 'unit_price_cents' => (int) $price], 'modifiers' => $modifiers];
            } else {
                $quote = app(OrderPricingService::class)->quote($line, true, $order->fulfillment, $order->customer?->lang ?? 'it');
            }
            $this->check(! $quote['attributes']['requires_production'] || Department::whereKey($quote['attributes']['department_id'])->where('production', true)->exists(), 'Un reparto produttivo non è più disponibile.');
            $quote['original_item_id'] = $item?->id;
            $quotes[] = $quote;
            $subtotal += $quote['attributes']['quantity'] * $quote['attributes']['unit_price_cents'];
        }

        return ['lines' => $quotes, 'subtotal' => $subtotal];
    }

    public function quote(Order $order, array $lines, array $details): array
    {
        Validator::make($details, ['date' => 'required|date_format:Y-m-d', 'time' => 'required|date_format:H:i', 'notes' => 'nullable|string|max:2000'])->validate();
        $quote = $this->priceLines($order, $lines);
        $this->check(in_array($details['time'], app(AvailabilityService::class)->slots($details['date'], $order->fulfillment), true), 'Scegli un orario disponibile per il servizio.');
        $settings = app(OrderingSettings::class)->get();
        $fee = 0;
        if ($order->fulfillment === 'delivery') {
            $this->check($quote['subtotal'] >= $settings['delivery_minimum_cents'], 'Il minimo per la consegna non è stato raggiunto.');
            $fee = $settings['delivery_free_over_cents'] > 0 && $quote['subtotal'] >= $settings['delivery_free_over_cents'] ? 0 : $settings['delivery_fee_cents'];
        }
        $quote['changes'] = [
            'requested_at' => Carbon::parse($details['date'].' '.$details['time'])->toDateTimeString(),
            'service_date' => $details['date'], 'customer_notes' => $details['notes'] ?? '',
            'delivery_fee_cents' => $fee, 'delivery_neighborhood' => $order->delivery_neighborhood,
        ];
        $quote['total'] = max(0, $quote['subtotal'] + $fee - (int) $order->discount_cents);

        return $quote;
    }

    public function fingerprint(Order $order): string
    {
        $data = $order->fresh()->only(['status', 'fulfillment_status', 'requested_at', 'service_date', 'customer_notes', 'delivery_fee_cents', 'delivery_address', 'delivery_neighborhood', 'customer_email', 'payment_method', 'discount_cents', 'discount_reason', 'auxiliary_vat']);
        $data['courses'] = $order->courses()->orderBy('id')->get()->map(fn ($c) => $c->only(['id', 'sent_at', 'rejected_at', 'service_percentage_bps']))->all();
        $data['items'] = $order->items()->with('modifiers')->orderBy('order_items.id')->get()->map(function ($item) {
            $attributes = $item->getAttributes();
            unset($attributes['updated_at']);
            $attributes['modifiers'] = $item->modifiers->sortBy('id')->map(fn ($m) => $m->getAttributes())->values()->all();

            return $attributes;
        })->all();

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }

    public function create(string $orderId, array $lines, array $details, string $message, int $expectedTotal, string $expectedFingerprint): CustomerOrderProposal
    {
        app(OrderService::class)->authorize(Permissions::CUSTOMER_ORDERS);
        Validator::make(['message' => $message], ['message' => 'required|string|max:4000'])->validate();

        return DB::transaction(function () use ($orderId, $lines, $details, $message, $expectedTotal, $expectedFingerprint) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $this->assertEditable($order);
            $this->check(! $order->pendingProposal()->exists(), 'Esiste già una proposta in attesa. Ritirala prima di inviarne un’altra.');
            $this->check(hash_equals($this->fingerprint($order), $expectedFingerprint), 'L’ordine è cambiato. Riapri la proposta per lavorare sui dati aggiornati.');
            $quote = $this->quote($order, $lines, $details);
            $this->check($quote['total'] === $expectedTotal, 'Il totale è cambiato. Controlla il nuovo riepilogo prima di inviare.');
            $this->check((bool) $order->customer_email, 'Il cliente non ha un indirizzo email.');
            $settings = app(OrderingSettings::class)->get();
            $deadline = Carbon::parse($quote['changes']['requested_at'])->subMinutes(max($settings['preparation_minutes'], $settings['lead_minutes']));
            $expires = $deadline->lessThan(now()->addDay()) ? $deadline : now()->addDay();
            $this->check($expires->isFuture(), 'L’orario è troppo vicino per attendere una risposta. Scegli un orario successivo.');
            $order->proposals()->where('status', 'pending')->where('expires_at', '<=', now())->update(['status' => 'expired']);
            $token = Str::random(64);
            $summary = app(OrderSummaryService::class);
            $proposal = $order->proposals()->create([
                'created_by' => auth()->id(), 'token_hash' => hash('sha256', $token), 'message' => trim($message),
                'payload' => $quote + ['before' => $summary->snapshot($order), 'summary' => $summary->proposed($order, $quote)],
                'order_fingerprint' => $expectedFingerprint, 'expires_at' => $expires,
            ]);
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'proposal_sent', 'details' => ['proposal' => $proposal->id, 'message' => trim($message)]]);
            app(OrderNotifications::class)->proposalSent($proposal, $token);

            return $proposal;
        }, 3);
    }

    public function find(string $token): CustomerOrderProposal
    {
        abort_unless(strlen($token) === 64, 404);

        return CustomerOrderProposal::with('order')->where('token_hash', hash('sha256', $token))->firstOrFail();
    }

    public function respond(string $token, string $choice): CustomerOrderProposal
    {
        Validator::make(['choice' => $choice], ['choice' => 'required|in:accept,reject'])->validate();
        $reference = $this->find($token);

        return DB::transaction(function () use ($reference, $choice) {
            $order = Order::lockForUpdate()->findOrFail($reference->order_id);
            $proposal = CustomerOrderProposal::lockForUpdate()->findOrFail($reference->id);
            $status = $choice === 'accept' ? 'accepted' : 'rejected';
            if ($proposal->status === $status) {
                return $proposal;
            }
            $this->check($proposal->isPending(), 'La proposta non è più disponibile. Contatta il ristorante.');
            $this->assertEditable($order);
            $this->check(hash_equals($proposal->order_fingerprint, $this->fingerprint($order)), 'L’ordine è cambiato. Contatta il ristorante prima di confermare.');
            if ($choice === 'accept') {
                $at = Carbon::parse($proposal->payload['changes']['requested_at']);
                $this->check(in_array($at->format('H:i'), app(AvailabilityService::class)->slots($at->toDateString(), $order->fulfillment), true), 'L’orario proposto non è più disponibile. Contatta il ristorante.');
                $this->apply($order, $proposal->payload);
            }
            $proposal->update(['status' => $status, 'responded_at' => now()]);
            $order->events()->create(['action' => 'proposal_'.$status, 'details' => ['proposal' => $proposal->id, 'customer' => $order->customer_name]]);
            app(OrderNotifications::class)->proposalResponded($proposal, $order->fresh());

            return $proposal->fresh();
        }, 3);
    }

    private function apply(Order $order, array $payload): void
    {
        $target = $order->courses()->firstOrFail();
        $all = $order->items()->with('modifiers')->get()->keyBy('id');
        $retained = [];
        foreach ($payload['lines'] as $line) {
            $attributes = $line['attributes'];
            $this->check(! $attributes['requires_production'] || Department::whereKey($attributes['department_id'])->where('production', true)->exists(), 'Un reparto non è più disponibile. Contatta il ristorante.');
            if ($attributes['department_id'] && ! Department::whereKey($attributes['department_id'])->exists()) {
                $attributes['department_id'] = null;
            }
            if ($attributes['product_id'] && ! DigitalMenuProduct::whereKey($attributes['product_id'])->exists()) {
                $attributes['product_id'] = null;
            }
            if ($attributes['menu_id'] && ! DigitalMenu::whereKey($attributes['menu_id'])->exists()) {
                $attributes['menu_id'] = null;
            }
            foreach ($line['modifiers'] as &$modifier) {
                if ($modifier['ingredient_id'] && ! DigitalMenuIngredient::whereKey($modifier['ingredient_id'])->exists()) {
                    $modifier['ingredient_id'] = null;
                }
            }
            unset($modifier);
            $item = isset($line['original_item_id']) ? $all->get($line['original_item_id']) : null;
            if ($item) {
                $item->update($attributes);
                $item->modifiers()->delete();
                $item->modifiers()->createMany($line['modifiers']);
            } else {
                $item = app(OrderPricingService::class)->append($target, ['attributes' => $attributes, 'modifiers' => $line['modifiers']]);
            }
            $retained[] = $item->id;
        }
        $order->items()->whereNotIn('order_items.id', $retained)->get()->each->delete();
        $order->update($payload['changes'] + ['fulfillment_status' => 'accepted']);
        $order->courses()->update(['customer_notes' => $payload['changes']['customer_notes']]);
    }

    public function withdraw(string $orderId, string $proposalId): void
    {
        app(OrderService::class)->authorize(Permissions::CUSTOMER_ORDERS);
        DB::transaction(function () use ($orderId, $proposalId) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $proposal = $order->proposals()->lockForUpdate()->findOrFail($proposalId);
            $this->check($proposal->isPending(), 'La proposta non è più in attesa.');
            $proposal->update(['status' => 'withdrawn', 'responded_at' => now()]);
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'proposal_withdrawn', 'details' => ['proposal' => $proposal->id]]);
            app(OrderNotifications::class)->proposalResponded($proposal, $order->fresh());
        }, 3);
    }

    private function check(bool $ok, string $message): void
    {
        if (! $ok) {
            throw ValidationException::withMessages(['proposal' => $message]);
        }
    }
}

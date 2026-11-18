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
        $this->check(! $order->fidelity_reward, __('fidelity.remove_reward_first'));
        $this->check(in_array($order->fulfillment, ['pickup', 'delivery']) && $order->status === 'open'
            && in_array($order->fulfillment_status, ['received', 'accepted']) && ! $order->payments()->exists()
            && ! $order->courses()->whereNotNull('sent_at')->exists(),
            __('customer_orders.messages.proposals_are_only_available_for_unpaid_orders_not_yet_in'));
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
                $this->check(($line['product_id'] ?? null) === $item->product_id, __('customer_orders.messages.to_replace_a_product_remove_its_line_and_add_the'));
                $attributes = $item->only(['product_id', 'menu_id', 'product_name', 'menu_name', 'department_id', 'department_name', 'requires_production', 'base_price_cents', 'vat']);
                if ($item->product_id) {
                    $product = DigitalMenuProduct::with('ingredients')->findOrFail($item->product_id);
                    [$price,$modifiers] = app(OrderPricingService::class)->modifiers($product, $item->base_price_cents, $line['added'] ?? [], $line['removed'] ?? [], $item);
                } else {
                    $this->check(empty($line['added']) && empty($line['removed']), __('customer_orders.messages.the_product_was_removed_from_the_catalog_only_quantity_and'));
                    $price = $item->unit_price_cents;
                    $modifiers = $item->modifiers->map(fn ($m) => $m->only(['ingredient_id', 'ingredient_name', 'action', 'price_cents']))->all();
                }
                $quote = ['attributes' => $attributes + ['quantity' => (int) $line['quantity'], 'notes' => $line['notes'] ?? '', 'unit_price_cents' => (int) $price], 'modifiers' => $modifiers];
            } else {
                $quote = app(OrderPricingService::class)->quote($line, true, $order->fulfillment, $order->customer_language ?: ($order->customer?->lang ?? 'it'));
            }
            $this->check(! $quote['attributes']['requires_production'] || Department::whereKey($quote['attributes']['department_id'])->where('production', true)->exists(), __('customer_orders.messages.a_production_department_is_no_longer_available'));
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
        $this->check(in_array($details['time'], app(AvailabilityService::class)->slots($details['date'], $order->fulfillment), true), __('customer_orders.messages.choose_an_available_time_for_the_service'));
        $settings = app(OrderingSettings::class)->get();
        $fee = 0;
        if ($order->fulfillment === 'delivery') {
            $this->check($quote['subtotal'] >= $settings['delivery_minimum_cents'], __('customer_orders.messages.the_delivery_minimum_has_not_been_reached'));
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
            $this->check(! $order->pendingProposal()->exists(), __('customer_orders.messages.a_proposal_is_already_pending_withdraw_it_before_sending_another'));
            $this->check(hash_equals($this->fingerprint($order), $expectedFingerprint), __('customer_orders.messages.the_order_changed_reopen_the_proposal_to_use_the_updated'));
            $quote = $this->quote($order, $lines, $details);
            $this->check($quote['total'] === $expectedTotal, __('customer_orders.messages.the_total_changed_check_the_new_summary_before_sending'));
            $this->check((bool) $order->customer_email, __('customer_orders.messages.the_customer_has_no_email_address'));
            $settings = app(OrderingSettings::class)->get();
            $deadline = Carbon::parse($quote['changes']['requested_at'])->subMinutes(max($settings['preparation_minutes'], $settings['lead_minutes']));
            $expires = $deadline->lessThan(now()->addDay()) ? $deadline : now()->addDay();
            $this->check($expires->isFuture(), __('customer_orders.messages.there_is_not_enough_time_to_wait_for_a_response'));
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
            $this->check($proposal->isPending(), __('customer_orders.messages.the_proposal_is_no_longer_available_contact_the_restaurant'));
            $this->assertEditable($order);
            $this->check(hash_equals($proposal->order_fingerprint, $this->fingerprint($order)), __('customer_orders.messages.the_order_changed_contact_the_restaurant_before_confirming'));
            if ($choice === 'accept') {
                $at = Carbon::parse($proposal->payload['changes']['requested_at']);
                $this->check(in_array($at->format('H:i'), app(AvailabilityService::class)->slots($at->toDateString(), $order->fulfillment), true), __('customer_orders.messages.the_proposed_time_is_no_longer_available_contact_the_restaurant'));
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
            $this->check(! $attributes['requires_production'] || Department::whereKey($attributes['department_id'])->where('production', true)->exists(), __('customer_orders.messages.a_department_is_no_longer_available_contact_the_restaurant'));
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
            $this->check($proposal->isPending(), __('customer_orders.messages.the_proposal_is_no_longer_pending'));
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

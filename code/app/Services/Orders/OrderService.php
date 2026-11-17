<?php

namespace App\Services\Orders;

use App\Models\DigitalMenuProduct;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\OrderItem;
use App\Models\ProductionTicket;
use App\Services\Fidelity\FidelityCheckout;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function authorize(string $permission = Permissions::ORDERS): void
    {
        abort_unless(auth()->user() && app(Permissions::class)->can(auth()->user(), $permission), 403);
    }

    public static function cents(string $amount): int
    {
        $negative = str_starts_with($amount, '-');
        $parts = explode('.', ltrim($amount, '-'));
        $value = (int) $parts[0] * 100 + (int) str_pad(substr($parts[1] ?? '', 0, 2), 2, '0');

        return $negative ? -$value : $value;
    }

    public function open(string $tableId, int $covers = 1, ?string $bookingId = null): Order
    {
        $this->authorize();

        return app(TableOrderService::class)->open($tableId, $covers, $bookingId);
    }

    public function addCourse(string $orderId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->create(['number' => (int) $order->courses()->max('number') + 1]);
            $this->event($order, 'course_created', ['course' => $course->number]);
        });
    }

    public function covers(string $orderId, int $covers): void
    {
        $this->authorize();
        Validator::make(compact('covers'), ['covers' => 'required|integer|min:1|max:1000'])->validate();
        DB::transaction(function () use ($orderId, $covers) {
            $order = $this->lockedOpen($orderId);
            $order->update(['covers' => $covers]);
            $this->event($order, 'covers_changed', compact('covers'));
        });
    }

    public function saveItem(string $orderId, string $courseId, string $categoryId, string $productId, int $quantity, string $notes = '', array $added = [], array $removed = [], ?string $itemId = null): void
    {
        $this->authorize();
        Validator::make(compact('quantity', 'notes', 'added', 'removed'), [
            'quantity' => 'required|integer|min:1|max:1000', 'notes' => 'string|max:2000',
            'added' => 'array|max:100', 'removed' => 'array|max:100', 'added.*' => 'uuid|distinct', 'removed.*' => 'uuid|distinct',
        ])->validate();
        DB::transaction(function () use ($orderId, $courseId, $categoryId, $productId, $quantity, $notes, $added, $removed, $itemId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->findOrFail($courseId);
            $this->editable($course);
            // Sent rows retain all original prices, tax and routing when notes/quantity change.
            if ($itemId) {
                $item = $course->items()->findOrFail($itemId);
                $before = $item->load('modifiers')->toArray();
                if ($item->product_id) {
                    $product = DigitalMenuProduct::with('ingredients')->findOrFail($item->product_id);
                    [$price, $modifiers] = app(OrderPricingService::class)->modifiers($product, $item->base_price_cents, $added, $removed, $item);
                    $item->update(['unit_price_cents' => $price]);
                    $item->modifiers()->delete();
                    $item->modifiers()->createMany($modifiers);
                }
                $item->update(['quantity' => $quantity, 'notes' => $notes]);
                $this->event($order, 'item_updated', ['item' => $item->id, 'product' => $item->product_name, 'quantity' => $quantity, 'notes' => $notes, 'before' => $before, 'after' => $item->fresh('modifiers')->toArray()]);

                return;
            }
            $this->check(! $course->sent_at && ! $course->submission_key, __('orders.messages.add_products_to_a_new_staff_course'));
            $quote = app(OrderPricingService::class)->quote([
                'category_id' => $categoryId, 'product_id' => $productId, 'quantity' => $quantity, 'notes' => $notes, 'added' => $added, 'removed' => $removed,
            ]);
            $item = app(OrderPricingService::class)->append($course, $quote);
            $this->event($order, 'item_added', ['item' => $item->id, 'product' => $item->product_name, 'quantity' => $quantity, 'snapshot' => $item->load('modifiers')->toArray()]);
        });
    }

    public function removeItem(string $orderId, string $itemId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId, $itemId) {
            $order = $this->lockedOpen($orderId);
            $item = $order->items()->findOrFail($itemId);
            $this->editable($item->course);
            $this->event($order, 'item_removed', ['item' => $item->id, 'product' => $item->product_name, 'quantity' => $item->quantity, 'unit_price_cents' => $item->unit_price_cents, 'snapshot' => $item->load('modifiers')->toArray()]);
            $ticketId = $item->production_ticket_id;
            $item->delete();
            if ($ticketId && ! OrderItem::where('production_ticket_id', $ticketId)->exists()) {
                ProductionTicket::whereKey($ticketId)->update(['status' => 'cancelled']);
            }
        });
    }

    public function send(string $orderId, string $courseId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId, $courseId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->with('items')->findOrFail($courseId);
            if ($course->sent_at) {
                return;
            }
            app(ProductionService::class)->send($course);
            $this->event($order, 'course_sent', ['course' => $course->number]);
            if ($course->customer_id) {
                app(\App\Services\PublicOrders\OrderNotifications::class)->courseAccepted($course);
            } else {
                app(\App\Services\Notification\NotificationService::class)->forPermissionTranslated(
                    Permissions::ORDERS, 'staff_order.sent', __('orders.messages.order_sent_by_staff'),
                    __('orders.messages.table_table_course_course_sent_to_departments'),
                    ['table' => $order->tables->pluck('name')->implode(', '), 'course' => $course->number], route('orders.show', $order)
                );
            }
        });
    }

    public function rejectCourse(string $orderId, string $courseId): void
    {
        $this->authorize();
        DB::transaction(function () use ($orderId, $courseId) {
            $order = $this->lockedOpen($orderId);
            $course = $order->courses()->findOrFail($courseId);
            $this->check($course->submission_key && ! $course->sent_at && ! $course->rejected_at, __('orders.messages.this_course_cannot_be_rejected'));
            $course->update(['rejected_at' => now()]);
            $this->event($order, 'course_rejected', ['course' => $course->number]);
            app(\App\Services\PublicOrders\OrderNotifications::class)->courseRejected($course);
        });
    }

    public function advance(string $ticketId, string $expectedStatus): void
    {
        $this->authorize(Permissions::KDS);
        DB::transaction(function () use ($ticketId, $expectedStatus) {
            $reference = ProductionTicket::with('course')->findOrFail($ticketId);
            $order = Order::lockForUpdate()->findOrFail($reference->course->order_id);
            $ticket = ProductionTicket::lockForUpdate()->findOrFail($ticketId);
            $this->check($order->status !== 'cancelled', __('cash_desk.order_cancelled'));
            $this->check($ticket->status === $expectedStatus, __('orders.messages.status_already_updated_refresh_the_screen'));
            $next = ['queued' => ['preparing', 'started_at'], 'preparing' => ['ready', 'ready_at'], 'ready' => ['served', 'served_at']][$ticket->status] ?? null;
            $this->check($next !== null, __('orders.messages.transition_not_allowed'));
            $ticket->update(['status' => $next[0], $next[1] => now()]);
            $this->event($order, 'production_advanced', ['ticket' => $ticketId, 'status' => $next[0]]);
        });
    }

    public function pay(string $orderId, string $method, int $expectedTotal, ?int $amount = null, int $tip = 0, ?string $key = null, ?string $payer = null, ?string $voucherIssuer = null, ?string $voucherReference = null, ?int $expectedBalance = null): void
    {
        $this->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('method', 'amount', 'tip', 'key', 'payer', 'voucherIssuer', 'voucherReference'), [
            'method' => 'required|in:manual,cash,card,voucher', 'amount' => 'nullable|integer|min:0|max:100000000',
            'tip' => 'integer|min:0|max:10000000', 'key' => 'nullable|uuid', 'payer' => 'nullable|string|max:100',
            'voucherIssuer' => 'nullable|string|max:100', 'voucherReference' => 'nullable|string|max:100',
        ])->validate();
        $this->check($method !== 'voucher' || $tip === 0, 'I buoni pasto non possono coprire le mance.');
        DB::transaction(function () use ($orderId, $method, $expectedTotal, $amount, $tip, $key, $payer, $voucherIssuer, $voucherReference, $expectedBalance) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $this->check($key === null || ! \App\Models\CashPaymentBatch::whereKey($key)->exists(), __('orders.messages.identifier_already_used_for_a_mixed_payment'));
            if ($key && ($existing = \App\Models\OrderPayment::where('idempotency_key', $key)->first())) {
                $this->check($existing->order_id === $orderId && $existing->method === $method
                    && ($amount === null || (int) $existing->amount_cents === $amount) && (int) $existing->tip_cents === $tip
                    && $existing->payer === $payer && $existing->voucher_issuer === $voucherIssuer && $existing->voucher_reference === $voucherReference,
                    __('orders.messages.payment_identifier_already_used_for_another_operation'));

                return;
            }
            if ($order->status === 'paid' && $amount === null) {
                return;
            }
            $this->check(! $order->billPlan()->exists(), __('orders.messages.collect_configured_bill_share'));
            $this->assertPayable($order, $expectedTotal, $expectedBalance);
            $balance = $order->balanceCents();
            $amount ??= $balance;
            $this->check($amount <= $balance && ($amount > 0 || $balance === 0), __('orders.messages.amount_exceeds_the_balance_or_is_invalid'));
            $this->check($method !== 'voucher' || filled($voucherIssuer) || $key === null, __('orders.messages.specify_the_meal_voucher_issuer'));
            $shift = \App\Models\CashShift::where('active_key', 'main')->lockForUpdate()->first();
            $order->update(['cash_snapshot' => app(CashTotalsService::class)->snapshot($order)]);
            $order->payments()->create(['user_id' => auth()->id(), 'amount_cents' => $amount, 'method' => $method,
                'tip_cents' => $tip, 'idempotency_key' => $key, 'cash_shift_id' => $shift?->id, 'payer' => $payer,
                'voucher_issuer' => $voucherIssuer, 'voucher_reference' => $voucherReference]);
            $this->event($order, 'payment_recorded', ['amount_cents' => $amount, 'method' => $method, 'tip_cents' => $tip, 'payer' => $payer]);
            if ($amount === $balance) {
                $order->update(['status' => 'paid', 'paid_at' => now()]);
                app(FidelityCheckout::class)->settle($order);
                DB::table('order_tables')->where('order_id', $orderId)->update(['active_table_id' => null]);
                $this->event($order, 'paid', ['amount_cents' => $expectedTotal, 'method' => $method]);
                if ($order->customer_email) {
                    app(\App\Services\PublicOrders\OrderNotifications::class)->status($order, 'paid');
                }
            }
        });
    }

    public function assertPayable(Order $order, int $expectedTotal, ?int $expectedBalance): void
    {
        $this->authorize(Permissions::CASH_REGISTER);
        $this->check($order->status === 'open', __('orders.messages.order_is_not_open'));
        $this->check(! $order->pendingProposal()->exists(), __('orders.messages.customer_response_required_before_payment'));
        $this->check($order->fulfillment === 'dine_in' || ! in_array($order->fulfillment_status, ['received', 'rejected', 'cancelled']), 'L’ordine deve essere accettato prima di incassare.');
        $this->check($order->fulfillment !== 'dine_in' || ! $order->courses()->whereNull('sent_at')->whereNull('rejected_at')->whereHas('items')->exists(), __('orders.messages.send_or_remove_draft_lines_before_collecting_payment'));
        $this->check($order->totalCents() === $expectedTotal, __('orders.messages.total_changed_review_amount'));
        app(FidelityCheckout::class)->assertReward($order);
        $balance = $order->balanceCents();
        $this->check($expectedBalance === null || $expectedBalance === $balance, __('orders.messages.the_balance_changed_check_payments_before_confirming'));
    }

    public function discount(string $orderId, int $value, string $reason, int $expectedTotal, bool $percentage = false, int $auxiliaryVat = 10): void
    {
        $this->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('value', 'reason', 'auxiliaryVat'), [
            'value' => 'integer|min:0|max:100000000', 'reason' => 'required|string|max:255', 'auxiliaryVat' => 'integer|in:0,4,5,10,22',
        ])->validate();
        DB::transaction(function () use ($orderId, $value, $reason, $expectedTotal, $percentage, $auxiliaryVat) {
            $order = $this->lockedOpen($orderId);
            $this->check($order->totalCents() === $expectedTotal, __('orders.messages.the_total_changed_refresh_the_bill'));
            $this->check(! $order->pendingProposal()->exists(), __('orders.messages.withdraw_proposal_before_discount_change'));
            $this->check($value === 0 || $order->coupon_disabled || ! \App\Models\CouponUsage::where('booking_id', $order->booking_id)->exists(), __('marketing.not_stackable'));
            $this->check(! $order->fidelity_reward || $value === 0, __('fidelity.existing_discount'));
            $gross = $order->grossCents();
            $this->check(! $percentage || $value <= 10000, __('orders.messages.the_percentage_must_be_between_0_and_100'));
            $discount = $percentage ? intdiv($gross * $value + 5000, 10000) : $value;
            $this->check($discount <= $gross, __('orders.messages.the_discount_exceeds_the_bill_total'));
            $order->update(['discount_cents' => $discount, 'discount_reason' => $reason, 'auxiliary_vat' => $auxiliaryVat]);
            $this->event($order, 'discount_changed', ['amount_cents' => $discount, 'notes' => $reason, 'auxiliary_vat' => $auxiliaryVat]);
        });
    }

    public function cancel(string $orderId): void
    {
        $this->authorize(Permissions::CASH_REGISTER);
        DB::transaction(function () use ($orderId) {
            $order = $this->lockedOpen($orderId);
            $this->check(! ProductionTicket::whereHas('course', fn ($q) => $q->where('order_id', $orderId))->whereNotIn('status', ['queued', 'cancelled'])->exists(), __('orders.messages.order_cancellation_blocked_by_preparation'));
            ProductionTicket::whereHas('course', fn ($q) => $q->where('order_id', $orderId))->update(['status' => 'cancelled']);
            app(FidelityCheckout::class)->release($order);
            $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            DB::table('order_tables')->where('order_id', $orderId)->update(['active_table_id' => null]);
            if ($order->fulfillment !== 'dine_in') {
                $order->update(['fulfillment_status' => 'cancelled']);
                $order->proposals()->where('status', 'pending')->update(['status' => 'withdrawn', 'responded_at' => now()]);
                app(\App\Services\PublicOrders\OrderNotifications::class)->status($order, 'cancelled');
            }
            $this->event($order, 'cancelled');
        });
    }

    private function lockedOpen(string $id): Order
    {
        $order = Order::lockForUpdate()->findOrFail($id);
        $this->check($order->status === 'open', __('orders.messages.the_order_is_closed'));
        $this->check($order->cash_snapshot === null && ! $order->payments()->exists(), __('orders.messages.bill_locked_after_the_first_payment'));

        return $order;
    }

    private function editable(OrderCourse $course): void
    {
        $this->check(! $course->rejected_at, __('orders.messages.this_course_was_rejected'));
        $this->check(! $course->tickets()->whereNotIn('status', ['queued', 'cancelled'])->exists(), __('orders.messages.course_locked_in_preparation'));
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }

    private function event(Order $order, string $action, array $details = []): void
    {
        $order->events()->create(['user_id' => auth()->id(), 'action' => $action, 'details' => $details]);
    }
}

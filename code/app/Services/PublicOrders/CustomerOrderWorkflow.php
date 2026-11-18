<?php

namespace App\Services\PublicOrders;

use App\Models\Order;
use App\Models\ProductionTicket;
use App\Services\Orders\OrderService;
use App\Services\Orders\ProductionService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomerOrderWorkflow
{
    public function transition(string $id, string $action, string $expected): void
    {
        app(OrderService::class)->authorize(Permissions::CUSTOMER_ORDERS);
        DB::transaction(function () use ($id, $action, $expected) {
            $order = Order::lockForUpdate()->findOrFail($id);
            abort_unless(in_array($order->fulfillment, ['pickup', 'delivery']), 404);
            $this->check(in_array($action, ['cancel', 'reject']) || ! $order->pendingProposal()->exists(), __('customer_orders.messages.wait_for_the_customers_response_or_withdraw_the_proposal_before'));
            $this->check($order->status !== 'cancelled' && $order->fulfillment_status === $expected, __('customer_orders.messages.the_status_changed_refresh_the_screen'));
            $next = match ($action) {
                'accept' => $expected === 'received' ? 'accepted' : null,
                'reject' => $expected === 'received' && $order->status !== 'paid' ? 'rejected' : null,
                'prepare' => $expected === 'accepted' ? 'preparing' : null,
                'ready' => $expected === 'preparing' ? 'ready' : null,
                'dispatch' => $expected === 'ready' && $order->fulfillment === 'delivery' ? 'out_for_delivery' : null,
                'complete' => ($expected === 'ready' && $order->fulfillment === 'pickup') || $expected === 'out_for_delivery' ? 'completed' : null,
                'cancel' => in_array($expected, ['received', 'accepted']) && $order->status !== 'paid' ? 'cancelled' : null,
                default => null,
            };
            $this->check($next !== null, __('customer_orders.messages.operation_not_allowed'));
            if ($next === 'preparing') {
                foreach ($order->courses()->whereHas('items')->get() as $course) {
                    app(ProductionService::class)->send($course);
                }
            }
            if ($next === 'ready') {
                $this->check(! ProductionTicket::whereHas('course', fn ($q) => $q->where('order_id', $id))->whereNotIn('status', ['ready', 'served', 'cancelled'])->exists(), 'I reparti non hanno ancora completato la preparazione.');
            }
            if (in_array($next, ['rejected', 'cancelled'])) {
                $this->check(! $order->payments()->exists(), __('customer_orders.messages.order_has_recorded_payments_cancellation_blocked'));
                app(\App\Services\Fidelity\FidelityCheckout::class)->release($order);
                $order->status = 'cancelled';
            }
            if ($next === 'completed') {
                ProductionTicket::whereHas('course', fn ($q) => $q->where('order_id', $id))->where('status', 'ready')->update(['status' => 'served', 'served_at' => now()]);
            }
            $order->fulfillment_status = $next;
            if ($order->status === 'cancelled') {
                $order->cancelled_at = now();
            }
            $order->save();
            if ($order->status === 'cancelled') {
                $order->proposals()->where('status', 'pending')->update(['status' => 'withdrawn', 'responded_at' => now()]);
            }
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'fulfillment_updated', 'details' => ['status' => $next]]);
            app(OrderNotifications::class)->status($order, $next);
        });
    }

    private function check(bool $ok, string $message): void
    {
        if (! $ok) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}

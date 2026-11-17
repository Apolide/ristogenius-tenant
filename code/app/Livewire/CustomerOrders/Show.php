<?php

namespace App\Livewire\CustomerOrders;

use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use App\Services\PublicOrders\CustomerOrderWorkflow;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public string $orderId;

    #[Locked]
    public int $displayedTotal = 0;

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::CUSTOMER_ORDERS);
    }

    public function mount(Order $order): void
    {
        abort_unless(in_array($order->fulfillment, ['pickup', 'delivery']), 404);
        $this->orderId = $order->id;
    }

    public function transition(string $action, string $expected): void
    {
        app(CustomerOrderWorkflow::class)->transition($this->orderId, $action, $expected);
    }

    public function withdrawProposal(string $id): void
    {
        app(\App\Services\PublicOrders\OrderProposalService::class)->withdraw($this->orderId, $id);
        session()->flash('order_notice', __('Proposta ritirata. L’ordine originale è rimasto invariato.'));
    }

    public function pay(): void
    {
        app(OrderService::class)->pay($this->orderId, 'cash', $this->displayedTotal);
        session()->flash('order_notice', __('Pagamento registrato.'));
    }

    public function render()
    {
        $order = Order::with(['courses.items.modifiers', 'courses.items.ticket', 'payments', 'events.user', 'pendingProposal', 'proposals.creator'])->findOrFail($this->orderId);
        $this->displayedTotal = $order->totalCents();

        return view('livewire.customer-orders.show', compact('order'))->title(__('Ordine ').$order->reference);
    }
}

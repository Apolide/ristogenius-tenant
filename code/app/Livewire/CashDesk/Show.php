<?php

namespace App\Livewire\CashDesk;

use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    #[Locked]
    public string $orderId;

    #[Locked]
    public int $displayedTotal = 0;

    public string $method = 'manual';

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
    }

    public function mount(Order $order): void
    {
        $this->orderId = $order->id;
    }

    public function pay(): void
    {
        app(OrderService::class)->pay($this->orderId, $this->method, $this->displayedTotal);
        session()->flash('order_notice', 'Pagamento registrato.');
    }

    public function cancel(): void
    {
        app(OrderService::class)->cancel($this->orderId);
        session()->flash('order_notice', 'Ordine annullato.');
    }

    public function render()
    {
        $order = Order::with(['courses.items.modifiers', 'courses.items.ticket', 'customer', 'payments', 'events.user'])->findOrFail($this->orderId);
        $this->displayedTotal = $order->totalCents();

        return view('livewire.cash-desk.show', compact('order'))->title('Cassa · '.$order->table_name);
    }
}

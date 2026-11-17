<?php

namespace App\Livewire\CustomerOrders;

use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use App\Services\PublicOrders\CustomerOrderWorkflow;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $date = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $type = '';

    #[Url]
    public bool $history = false;

    protected function cash(): bool
    {
        return false;
    }

    public function boot(): void
    {
        app(OrderService::class)->authorize($this->cash() ? Permissions::CASH_REGISTER : Permissions::CUSTOMER_ORDERS);
    }

    public function mount(): void
    {
        $this->date = $this->date ?: today()->toDateString();
    }

    public function updated($name): void
    {
        if (in_array($name, ['date', 'search', 'status', 'type', 'history'])) {
            $this->resetPage();
        }
    }

    public function previousDay(): void
    {
        $this->date = \Carbon\Carbon::parse($this->date)->subDay()->toDateString();
        $this->resetPage();
    }

    public function nextDay(): void
    {
        $this->date = \Carbon\Carbon::parse($this->date)->addDay()->toDateString();
        $this->resetPage();
    }

    public function transition(string $id, string $action, string $expected): void
    {
        abort_if($this->cash(), 403);
        app(CustomerOrderWorkflow::class)->transition($id, $action, $expected);
    }

    public function render()
    {
        $this->validate(['date' => 'required|date_format:Y-m-d', 'search' => 'max:100', 'type' => 'in:,pickup,delivery', 'status' => 'in:,received,accepted,preparing,ready,out_for_delivery,completed,rejected,cancelled,awaiting_customer']);
        $query = Order::whereIn('fulfillment', ['pickup', 'delivery'])->where(function ($q) {
            $q->whereDate('service_date', $this->date);
            if ($this->cash()) {
                $q->orWhere('status', 'open');
            }
        })
            ->when($this->type, fn ($q) => $q->where('fulfillment', $this->type))->when($this->status, fn ($q) => $this->status === 'awaiting_customer' ? $q->whereHas('pendingProposal') : $q->where('fulfillment_status', $this->status))
            ->when(! $this->history, function ($q) {
                if ($this->cash()) {
                    $q->where('status', '!=', 'cancelled')->where(fn ($q) => $q->where('status', 'open')->orWhereNotIn('fulfillment_status', ['completed', 'rejected', 'cancelled']));
                } else {
                    $q->whereNotIn('fulfillment_status', ['completed', 'rejected', 'cancelled']);
                }
            })
            ->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('reference', 'like', '%'.$this->search.'%')->orWhere('customer_name', 'like', '%'.$this->search.'%')->orWhere('customer_phone', 'like', '%'.$this->search.'%')));

        return view('livewire.customer-orders.index', ['orders' => $query->with(['courses.items', 'payments', 'pendingProposal'])->orderBy('requested_at')->paginate(18), 'cash' => $this->cash()])->title($this->cash() ? __('Cassa · Asporto e consegne') : __('Ordini Clienti'));
    }
}

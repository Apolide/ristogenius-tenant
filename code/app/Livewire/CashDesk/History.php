<?php

namespace App\Livewire\CashDesk;

use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class History extends Component
{
    use WithPagination;

    #[Url]
    public string $date = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $search = '';

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
    }

    public function mount(): void
    {
        $this->date = $this->date ?: now()->toDateString();
    }

    public function updated($name): void
    {
        if (in_array($name, ['date', 'status', 'search'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $this->validate(['date' => 'required|date_format:Y-m-d', 'status' => 'in:,open,paid,cancelled', 'search' => 'max:100']);
        $query = Order::whereDate('service_date', $this->date)->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('table_name', 'like', '%'.$this->search.'%')->orWhere('room_name', 'like', '%'.$this->search.'%')->orWhereHas('customer', fn ($q) => $q->where('display_name', 'like', '%'.$this->search.'%')->orWhere('firstname', 'like', '%'.$this->search.'%')->orWhere('lastname', 'like', '%'.$this->search.'%'));
            }));
        $paidCents = (int) \App\Models\OrderPayment::whereIn('order_id', (clone $query)->select('id'))->sum('amount_cents');

        return view('livewire.cash-desk.history', ['orders' => $query->with(['courses.items', 'payments', 'customer'])->latest()->paginate(20), 'paidCents' => $paidCents])->title('Storico cassa');
    }
}

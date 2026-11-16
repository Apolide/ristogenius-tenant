<?php

namespace App\Livewire\CashDesk;

use App\Models\CashShift;
use App\Services\Orders\CashShiftService;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Livewire\Component;
use Livewire\WithPagination;

class Shifts extends Component
{
    use WithPagination;

    public string $opening = '0.00';

    public string $counted = '0.00';

    public string $notes = '';

    public function boot(): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
    }

    private function cents(string $field): int
    {
        $this->validate([$field => ['required', 'regex:/^\d{1,6}([.,]\d{1,2})?$/']]);

        return OrderService::cents(str_replace(',', '.', $this->{$field}));
    }

    public function open(): void
    {
        app(CashShiftService::class)->open($this->cents('opening'));
        session()->flash('order_notice', 'Turno aperto.');
    }

    public function close(string $id): void
    {
        app(CashShiftService::class)->close($id, $this->cents('counted'), $this->notes);
        $this->reset('counted', 'notes');
        session()->flash('order_notice', 'Turno chiuso e riconciliato.');
    }

    public function render()
    {
        $active = CashShift::where('active_key', 'main')->first();
        $breakdown = $active?->payments()->selectRaw('method, SUM(amount_cents) as amount, SUM(tip_cents) as tips')->groupBy('method')->get();

        return view('livewire.cash-desk.shifts', [
            'active' => $active, 'breakdown' => $breakdown,
            'shifts' => CashShift::whereNotNull('closed_at')->latest('closed_at')->paginate(15),
        ])->title('Turni di cassa');
    }
}

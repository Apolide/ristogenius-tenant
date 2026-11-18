<?php

namespace App\Livewire\CashDesk;

use App\Services\Orders\CashReconciliationService;
use Livewire\Component;

class Reconciliation extends Component
{
    public string $from;

    public string $to;

    public function mount(): void
    {
        $this->from = $this->to = today()->toDateString();
    }

    public function export()
    {
        $service = app(CashReconciliationService::class);
        $csv = $service->csv($service->report($this->from, $this->to));

        return response()->streamDownload(fn () => print ($csv), 'riconciliazione-cassa.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function render()
    {
        return view('livewire.cash-desk.reconciliation', app(CashReconciliationService::class)->report($this->from, $this->to))->title(__('cash_desk.cash_reconciliation'));
    }
}

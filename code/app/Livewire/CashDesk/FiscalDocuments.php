<?php

namespace App\Livewire\CashDesk;

use App\Models\FiscalDocument;
use App\Models\FiscalOperation;
use App\Models\FiscalProfile;
use App\Models\Order;
use App\Services\Fiscal\FiscalDocumentService;
use App\Services\Fiscal\FiscalWorkstationService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class FiscalDocuments extends Component
{
    #[Locked]
    public string $orderId;

    public string $storeId = '';

    public string $registerId = '';

    public string $manualMethod = '';

    public int $voucherCount = 0;

    public array $remoteIds = [];

    public array $available = [];

    public int $returnItemId = 0;

    public int $returnQuantity = 1;

    public array $remoteDocuments = [];

    public int $remoteOffset = 0;

    public string $remoteKind = 'receipt';

    public string $invoiceNumber = '';

    public function boot(): void
    {
        app(FiscalDocumentService::class)->authorize();
    }

    public function mount(Order $order): void
    {
        $this->orderId = $order->id;
        $voucherPayments = $order->payments()->where('method', 'voucher')->where('amount_cents', '>', 0)->get();
        if ($voucherPayments->isNotEmpty() && $voucherPayments->every(fn ($payment) => $payment->voucher_quantity > 0)) {
            $this->voucherCount = (int) $voucherPayments->sum('voucher_quantity');
        }
        $defaults = app(FiscalWorkstationService::class)->defaults();
        $this->storeId = $defaults['store_id'];
        $this->registerId = $defaults['register_id'];
        $this->manualMethod = $order->payments()->where('method', 'manual')->where('amount_cents', '>', 0)->exists() ? $defaults['manual_method'] : '';
    }

    public function updatedStoreId(): void
    {
        $registers = app(FiscalWorkstationService::class)->registers(app(FiscalWorkstationService::class)->stores()->firstWhere('id', $this->storeId));
        $this->registerId = $registers->count() === 1 ? $registers->first()['cr_id'] : '';
        $this->remoteDocuments = [];
        $this->remoteOffset = 0;
    }

    public function saveWorkstation(): void
    {
        $hasManual = Order::findOrFail($this->orderId)->payments()->where('method', 'manual')->where('amount_cents', '>', 0)->exists();
        app(FiscalWorkstationService::class)->remember($this->storeId, $this->registerId, $hasManual ? $this->manualMethod : null);
        session()->flash('order_notice', __('Postazione predefinita salvata per il tuo utente.'));
    }

    public function issue(): void
    {
        app(FiscalDocumentService::class)->issue($this->orderId, $this->storeId, $this->registerId, $this->manualMethod, $this->voucherCount);
        session()->flash('order_notice', __('Richiesta fiscale sandbox registrata. Controlla lo stato del documento.'));
    }

    private function orderDocument(string $id): FiscalDocument
    {
        return FiscalDocument::where('order_id', $this->orderId)->findOrFail($id);
    }

    public function refreshDocument(string $id): void
    {
        $this->orderDocument($id);
        app(FiscalDocumentService::class)->refresh($id, $this->remoteIds[$id] ?? null);
        session()->flash('order_notice', __('Documento verificato.'));
    }

    public function availableReturns(string $id): void
    {
        $this->orderDocument($id);
        $this->available = app(FiscalDocumentService::class)->returnable($id);
    }

    public function returnDocument(string $id): void
    {
        $this->orderDocument($id);
        app(FiscalDocumentService::class)->reverse($id, 'return', [['id' => $this->returnItemId, 'quantity' => $this->returnQuantity]]);
        $this->available = [];
        session()->flash('order_notice', __('Reso fiscale sandbox registrato. Il rimborso del pagamento va gestito separatamente.'));
    }

    public function voidDocument(string $id): void
    {
        $this->orderDocument($id);
        app(FiscalDocumentService::class)->reverse($id, 'void');
        session()->flash('order_notice', __('Annullamento fiscale sandbox registrato. Gli incassi restano nello storico gestionale.'));
    }

    public function searchRemote(): void
    {
        $this->validate(['remoteKind' => 'in:receipt,invoice', 'remoteOffset' => 'integer|min:0|max:1000000', 'invoiceNumber' => 'string|max:20']);
        $this->remoteDocuments = $this->remoteKind === 'invoice'
            ? app(\App\Services\Fiscal\FiscalSetupService::class)->read('invoices', ['fiscal_id' => app(\App\Services\Fiscal\FiscalSetupService::class)->profile()->fiscal_id, 'number' => $this->invoiceNumber, 'skip' => $this->remoteOffset])['documents']
            : app(FiscalDocumentService::class)->remoteDocuments($this->storeId, $this->remoteOffset);
    }

    public function render()
    {
        return view('livewire.cash-desk.fiscal-documents', [
            'order' => Order::findOrFail($this->orderId), 'profile' => FiscalProfile::where('environment', 'sandbox')->first(),
            'stores' => app(FiscalWorkstationService::class)->stores(),
            'canSwitchWorkstation' => app(FiscalWorkstationService::class)->defaults()['can_switch'],
            'hasManualPayments' => Order::findOrFail($this->orderId)->payments()->where('method', 'manual')->where('amount_cents', '>', 0)->exists(),
            'hasVouchers' => Order::findOrFail($this->orderId)->payments()->where('method', 'voucher')->where('amount_cents', '>', 0)->exists(),
            'documents' => FiscalDocument::where('order_id', $this->orderId)->oldest()->get(),
            'operations' => FiscalOperation::whereIn('fiscal_document_id', FiscalDocument::where('order_id', $this->orderId)->select('id'))->latest()->get(),
        ])->title(__('Documenti commerciali · Sandbox'));
    }
}

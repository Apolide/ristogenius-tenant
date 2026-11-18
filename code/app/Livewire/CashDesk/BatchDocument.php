<?php

namespace App\Livewire\CashDesk;

use App\Models\CashPaymentBatch;
use App\Models\FiscalDocument;
use App\Models\FiscalProfile;
use App\Models\FiscalRecipient;
use App\Services\Fiscal\FiscalDocumentService;
use App\Services\Fiscal\FiscalInvoiceService;
use App\Services\Fiscal\FiscalSetupService;
use App\Services\Fiscal\FiscalWorkstationService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BatchDocument extends Component
{
    #[Locked]
    public string $batchId;

    public string $storeId = '';

    public string $registerId = '';

    public bool $invoiceForm = false;

    #[Locked]
    public string $refundKey;

    public string $refundAmount = '';

    public string $refundMethod = 'cash';

    public string $refundReason = '';

    public string $refundReference = '';

    public string $remoteId = '';

    public string $copyEmail = '';

    public bool $copyAgreed = false;

    public string $recipientId = '';

    public array $buyer = ['name' => '', 'vat_id' => '', 'tax_code' => '', 'address' => '', 'zip' => '', 'city' => '', 'province' => '', 'sdi' => '0000000', 'pec' => ''];

    public function boot(): void
    {
        app(FiscalDocumentService::class)->authorize();
    }

    public function mount(string $batchId): void
    {
        $this->refundKey = (string) \Illuminate\Support\Str::uuid();
        $this->batchId = CashPaymentBatch::findOrFail($batchId)->id;
        $defaults = app(FiscalWorkstationService::class)->defaults();
        $this->storeId = $defaults['store_id'];
        $this->registerId = $defaults['register_id'];
    }

    public function updatedStoreId(): void
    {
        $registers = app(FiscalWorkstationService::class)->registers(app(FiscalWorkstationService::class)->stores()->firstWhere('id', $this->storeId));
        $this->registerId = $registers->count() === 1 ? $registers->first()['cr_id'] : '';
    }

    public function updatedRecipientId(): void
    {
        if ($this->recipientId !== '') {
            $this->buyer = FiscalRecipient::where('fiscal_profile_id', app(FiscalSetupService::class)->profile()->id)->findOrFail($this->recipientId)->data;
        }
    }

    public function receipt(): void
    {
        app(FiscalDocumentService::class)->issueBatch($this->batchId, $this->storeId, $this->registerId);
        app(\App\Services\Fiscal\FiscalCopyService::class)->queueReady(1, $this->batchId);
    }

    public function invoice(): void
    {
        app(FiscalInvoiceService::class)->issue($this->batchId, $this->buyer, $this->storeId, $this->registerId);
        app(\App\Services\Fiscal\FiscalCopyService::class)->queueReady(1, $this->batchId);
        $this->invoiceForm = false;
    }

    public function verify(): void
    {
        $document = FiscalDocument::where('coverage_key', 'batch:'.$this->batchId)->firstOrFail();
        app(FiscalDocumentService::class)->refresh($document->id, $this->remoteId ?: null);
        app(\App\Services\Fiscal\FiscalCopyService::class)->queueReady(1, $this->batchId);
    }

    public function credit(): void
    {
        $document = FiscalDocument::where('coverage_key', 'batch:'.$this->batchId)->firstOrFail();
        app(FiscalInvoiceService::class)->credit($document->id);
    }

    public function requestCopy(): void
    {
        app(\App\Services\Fiscal\FiscalCopyService::class)->request($this->batchId, $this->copyEmail, $this->copyAgreed);
        app(\App\Services\Fiscal\FiscalCopyService::class)->queueReady(1, $this->batchId);
    }

    public function refund(): void
    {
        $this->validate(['refundAmount' => ['required', 'regex:/^\d{1,7}([.,]\d{1,2})?$/']]);
        app(\App\Services\Orders\CashRefundService::class)->record($this->batchId, $this->refundKey, \App\Services\Orders\OrderService::cents(str_replace(',', '.', $this->refundAmount)), $this->refundMethod, $this->refundReason, $this->refundReference);
        $this->refundKey = (string) \Illuminate\Support\Str::uuid();
        $this->refundAmount = '';
    }

    public function render()
    {
        $batch = CashPaymentBatch::findOrFail($this->batchId);
        $profile = FiscalProfile::where('environment', 'sandbox')->first();

        return view('livewire.cash-desk.batch-document', [
            'copyRequest' => \App\Models\FiscalCopyRequest::where('cash_payment_batch_id', $this->batchId)->first(),
            'refunds' => \App\Models\CashRefund::where('cash_payment_batch_id', $this->batchId)->get(),
            'document' => FiscalDocument::where('coverage_key', 'batch:'.$this->batchId)->first(),
            'wholeDocument' => FiscalDocument::where('sale_order_id', $batch->order_id)->whereNotIn('status', ['draft', 'rejected'])->first(),
            'profile' => $profile, 'stores' => app(FiscalWorkstationService::class)->stores(),
            'canSwitch' => app(FiscalWorkstationService::class)->defaults()['can_switch'],
            'recipients' => $profile ? FiscalRecipient::where('fiscal_profile_id', $profile->id)->orderBy('name')->get() : collect(),
            'corrections' => FiscalDocument::whereIn('parent_id', FiscalDocument::where('coverage_key', 'batch:'.$this->batchId)->select('id'))->get(),
        ]);
    }
}

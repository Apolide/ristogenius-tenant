<?php

namespace App\Services\Fiscal;

use App\Models\CashPaymentBatch;
use App\Models\FiscalDocument;
use App\Models\FiscalOperation;
use App\Models\FiscalRecipient;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FiscalInvoiceService
{
    public function __construct(private FiscalSetupService $setup, private FiscalOperationRunner $runner, private ItalianInvoiceXml $xml) {}

    public function issue(string $batchId, array $buyer, string $storeId, string $register): FiscalDocument
    {
        app(FiscalDocumentService::class)->authorize();
        $buyer = $this->xml->recipient($buyer);
        $store = app(FiscalDocumentService::class)->store($storeId);
        $operation = $this->runner->run('store:'.$storeId, 'send_invoice', function (FiscalOperation $operation) use ($batchId, $buyer, $store, $register) {
            $reference = CashPaymentBatch::findOrFail($batchId);
            $order = Order::lockForUpdate()->findOrFail($reference->order_id);
            $batch = CashPaymentBatch::lockForUpdate()->findOrFail($batchId);
            app(FiscalCoverageService::class)->assertBatchAvailable($batch);
            $profile = $this->setup->profile();
            $this->setup->check($profile->invoice_enabled, 'Abilita prima la fatturazione elettronica sandbox nelle impostazioni fiscali.');
            app(FiscalDocumentService::class)->context($store, $register);
            if (app(FiscalWorkstationService::class)->defaults()['can_switch']) {
                app(FiscalWorkstationService::class)->remember($store->id, $register);
            }
            $existing = FiscalDocument::where('coverage_key', 'batch:'.$batchId)->lockForUpdate()->first();
            $this->setup->check(! $existing || $existing->document_kind === 'invoice' && in_array($existing->status, ['draft', 'rejected']), 'Incasso già documentato o con invio da verificare.');
            app(FiscalBatchAllocationService::class)->ensure($order);
            $scope = $batch->fresh()->fiscal_snapshot;
            $this->setup->check($batch->amount_cents > 0, 'Incasso senza importo fatturabile.');
            $number = $existing?->document_number ?? $this->number($profile->invoice_series);
            $date = $existing?->document_date?->toDateString() ?? now()->toDateString();
            $xml = $this->xml->build($profile, $scope, $buyer, $number, $date);
            $attributes = ['order_id' => $order->id, 'cash_payment_batch_id' => $batchId, 'coverage_key' => 'batch:'.$batchId,
                'fiscal_store_id' => $store->id, 'user_id' => auth()->id(), 'provider' => 'openapi', 'environment' => 'sandbox',
                'fiscal_id' => $profile->fiscal_id, 'type' => 'sale', 'document_kind' => 'invoice', 'status' => 'submitting',
                'document_number' => $number, 'document_date' => $date, 'total_cents' => $batch->amount_cents,
                'snapshot' => ['scope' => $scope, 'buyer' => $buyer, 'xml' => $xml]];
            $document = $existing ?? new FiscalDocument(['id' => (string) Str::uuid()]);
            $document->fill($attributes)->save();
            FiscalRecipient::updateOrCreate(['fiscal_profile_id' => $profile->id, 'tax_key' => hash('sha256', ($buyer['vat_id'] ?? '').':'.($buyer['tax_code'] ?? ''))], ['name' => $buyer['name'], 'data' => $buyer]);
            $operation->update(['fiscal_document_id' => $document->id]);

            return ['xml' => $xml];
        }, fn ($result, $operation) => $this->apply($operation->fiscal_document_id, $result));

        return FiscalDocument::findOrFail($operation->fiscal_document_id);
    }

    private function number(string $series): string
    {
        $year = (int) now()->year;
        DB::table('fiscal_sequences')->insertOrIgnore(['year' => $year, 'series' => $series, 'last_number' => 0]);
        $sequence = DB::table('fiscal_sequences')->where(compact('year', 'series'))->lockForUpdate()->first();
        $next = $sequence->last_number + 1;
        $number = $series.'/'.$year.'/'.$next;
        $this->setup->check(strlen($number) <= 20, 'Numerazione fattura oltre il limite consentito.');
        DB::table('fiscal_sequences')->where('id', $sequence->id)->update(['last_number' => $next]);

        return $number;
    }

    public function refresh(string $id, ?string $remoteId = null): void
    {
        $service = app(FiscalDocumentService::class);
        $service->authorize();
        $document = $service->document($id);
        $this->setup->check($document->document_kind === 'invoice', 'Documento non fattura.');
        $this->reconcile($document, $remoteId);
    }

    private function reconcile(FiscalDocument $document, ?string $remoteId = null): void
    {
        $id = $document->id;
        $remoteId = $document->remote_id ?? $remoteId;
        $this->setup->check(is_string($remoteId) && preg_match('/^[a-zA-Z0-9_-]{1,100}$/', $remoteId), 'Inserisci l’identificativo remoto per verificare l’invio.');
        $operation = FiscalOperation::where('fiscal_document_id', $id)->whereNotNull('active_key')->first();
        $this->setup->check(! $operation || $operation->status === 'unknown' || $operation->created_at->lt(now()->subSeconds((int) config('fiscal.openapi.timeout') + 15)), 'Invio ancora in corso.');
        $result = $this->setup->read('invoice', ['id' => $remoteId]);
        $this->setup->check(($result['id'] ?? null) === $remoteId, 'Identificativo fattura incoerente.');
        DB::transaction(function () use ($id, $result, $operation) {
            $this->apply($id, $result);
            $operation?->update(['status' => 'reconciled', 'active_key' => null, 'response' => $result, 'error' => null]);
        });
    }

    /** Read-only remote polling; never re-sends an invoice or a payment. */
    public function synchronizePending(int $limit = 25): array
    {
        $synced = $failed = 0;
        foreach (FiscalDocument::where('environment', 'sandbox')->where('document_kind', 'invoice')->where('status', 'pending')->whereNotNull('remote_id')->oldest('updated_at')->limit(max(1, min(100, $limit)))->get() as $document) {
            try {
                $this->reconcile($document);
                $synced++;
            } catch (\Illuminate\Validation\ValidationException) {
                $failed++;
            }
        }

        return compact('synced', 'failed');
    }

    private function apply(string $id, array $result): void
    {
        $doc = FiscalDocument::lockForUpdate()->findOrFail($id);
        $this->setup->check(filled($result['id'] ?? null) && ($result['fiscal_id'] ?? null) === $doc->fiscal_id
            && ($result['direction'] ?? null) === 'outgoing' && ($result['type'] ?? null) === ($doc->type === 'credit' ? 'TD04' : 'TD01')
            && ($result['document_number'] ?? null) === $doc->document_number, 'La risposta non appartiene alla fattura attesa.');
        $total = $result['total_amount_including_tax'] ?? $result['total_gross_amount'] ?? null;
        $this->setup->check(is_numeric($total) && (int) round((float) $total * 100) === $doc->total_cents, 'Totale fattura remoto incoerente.');
        $this->setup->check(! FiscalDocument::where('remote_id', $result['id'])->whereKeyNot($id)->exists(), 'Fattura remota già associata.');
        $this->setup->check(($result['issue_date'] ?? null) === $doc->document_date->toDateString(), 'Data fattura remota incoerente.');
        foreach (['vat_id', 'tax_code'] as $field) {
            $expected = $doc->snapshot['buyer'][$field] ?? '';
            if (filled($expected)) {
                $this->setup->check(preg_replace('/^IT/', '', (string) ($result['recipient'][$field] ?? '')) === $expected, 'Destinatario fattura remoto incoerente.');
            }
        }
        $sdi = $result['details']['sdi_status'] ?? '';
        $state = $result['state'] ?? '';
        $this->setup->check(in_array($state, ['NEW', 'SENT', 'DONE', 'ERROR']), 'Stato fattura non riconosciuto.');
        $previousStatus = $doc->status;
        $doc->update(['remote_id' => $result['id'], 'provider_status' => $state, 'remote_data' => $result,
            'status' => ($state === 'ERROR' || $sdi === 'ERROR') ? 'sdi_rejected' : (in_array($sdi, ['DELIVERED', 'NOT_DELIVERED'], true) ? 'issued' : ($previousStatus === 'issued' ? 'issued' : 'pending'))]);
        if ($previousStatus !== $doc->status) {
            Order::findOrFail($doc->order_id)->events()->create(['user_id' => auth()->id(), 'action' => 'fiscal_updated', 'details' => ['document' => $doc->id, 'type' => $doc->type, 'status' => $doc->status]]);
        }
    }

    public function credit(string $id): FiscalDocument
    {
        $service = app(FiscalDocumentService::class);
        $service->authorize();
        $parent = $service->document($id);
        $this->setup->check($parent->document_kind === 'invoice' && $parent->type === 'sale' && $parent->status === 'issued', 'Fattura non disponibile per nota di credito.');
        $operation = $this->runner->run('store:'.$parent->fiscal_store_id, 'send_invoice', function (FiscalOperation $operation) use ($parent) {
            $parent = FiscalDocument::lockForUpdate()->findOrFail($parent->id);
            $this->setup->check($parent->status === 'issued', 'Lo stato della fattura è cambiato.');
            $child = FiscalDocument::where('parent_id', $parent->id)->lockForUpdate()->first();
            $this->setup->check(! $child || in_array($child->status, ['draft', 'rejected'], true), 'Nota di credito già presente: verificane l’esito.');
            app(FiscalWorkstationService::class)->assertSelection($parent->fiscal_store_id);
            $number = $child?->document_number ?? $this->number($this->setup->profile()->invoice_series);
            $date = $child?->document_date?->toDateString() ?? now()->toDateString();
            $snapshot = $parent->snapshot;
            $snapshot['xml'] = $this->xml->build($this->setup->profile(), $snapshot['scope'], $snapshot['buyer'], $number, $date, ['number' => $parent->document_number, 'date' => $parent->document_date->toDateString()]);
            $child ??= new FiscalDocument;
            $child->fill(['order_id' => $parent->order_id, 'parent_id' => $parent->id, 'fiscal_store_id' => $parent->fiscal_store_id,
                'user_id' => auth()->id(), 'provider' => 'openapi', 'environment' => 'sandbox', 'fiscal_id' => $parent->fiscal_id, 'type' => 'credit', 'document_kind' => 'invoice',
                'status' => 'submitting', 'total_cents' => $parent->total_cents, 'document_number' => $number, 'document_date' => $date, 'snapshot' => $snapshot])->save();
            $operation->update(['fiscal_document_id' => $child->id]);

            return ['xml' => $snapshot['xml']];
        }, fn ($result, $operation) => $this->apply($operation->fiscal_document_id, $result));

        return FiscalDocument::findOrFail($operation->fiscal_document_id);
    }
}

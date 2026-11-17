<?php

namespace App\Services\Fiscal;

use App\Models\FiscalCashier;
use App\Models\FiscalDocument;
use App\Models\FiscalOperation;
use App\Models\FiscalStore;
use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FiscalDocumentService
{
    public function __construct(private FiscalSetupService $setup, private FiscalOperationRunner $runner, private FiscalReceiptBuilder $builder) {}

    public function authorize(): void
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
    }

    public function store(string $id): FiscalStore
    {
        return FiscalStore::where('fiscal_profile_id', $this->setup->profile()->id)->findOrFail($id);
    }

    public function context(FiscalStore $store, string $register): array
    {
        app(FiscalWorkstationService::class)->assertSelection($store->id, $register);
        $profile = $this->setup->profile();
        $store->refresh();
        $this->setup->check($profile->enabled && $store->active && $store->status === 'ONLINE' && filled($store->pem_serial), __('Sede non pronta: sincronizza e attendi il completamento dell’attivazione.'));
        $cr = collect($store->registers)->firstWhere('cr_id', $register);
        $this->setup->check($cr && ($cr['status'] ?? '') === 'ONLINE', __('Il registratore selezionato non è operativo in questa sede.'));
        $cashier = FiscalCashier::where('fiscal_profile_id', $profile->id)->where('user_id', auth()->id())->first();
        $this->setup->check($cashier && filled($cashier->remote_id) && strtolower($cashier->status) === 'active', __('Il tuo utente non è associato a un cassiere fiscale attivo.'));

        return ['fiscal_id' => $profile->fiscal_id, 'store_id' => $store->remote_id, 'register_id' => $register, 'cashier_id' => $cashier->remote_id];
    }

    public function issue(string $orderId, string $storeId, string $register, string $manualMethod = '', int $voucherCount = 0): FiscalDocument
    {
        $this->authorize();
        app(FiscalWorkstationService::class)->assertSelection($storeId, $register);
        Validator::make(compact('storeId', 'register', 'manualMethod', 'voucherCount'), ['storeId' => 'required|uuid', 'register' => 'required|string|max:100', 'manualMethod' => 'in:,cash,card', 'voucherCount' => 'integer|min:0|max:10000'])->validate();
        $store = $this->store($storeId);
        $operation = $this->runner->run('store:'.$store->id, 'issue', function (FiscalOperation $operation) use ($orderId, $store, $register, $manualMethod, $voucherCount) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            app(FiscalCoverageService::class)->assertWholeAvailable($orderId);
            $document = FiscalDocument::where('sale_order_id', $orderId)->lockForUpdate()->first();
            $this->setup->check(! $document || in_array($document->status, ['draft', 'rejected'], true), __('Documento già inviato o con esito da verificare: non reinviare.'));
            $context = $this->context($store, $register);
            $localId = $document?->id ?? (string) Str::uuid();
            $snapshot = $this->builder->build($order, $this->setup->profile(), $context + ['local_id' => $localId], $manualMethod, $voucherCount);
            $workstations = app(FiscalWorkstationService::class);
            if ($workstations->defaults()['can_switch']) {
                $workstations->remember($store->id, $register);
            }
            $attributes = ['order_id' => $orderId, 'sale_order_id' => $orderId, 'fiscal_store_id' => $store->id, 'user_id' => auth()->id(),
                'provider' => 'openapi', 'environment' => 'sandbox', 'fiscal_id' => $context['fiscal_id'], 'type' => 'sale', 'status' => 'submitting',
                'total_cents' => $order->totalCents(), 'snapshot' => $snapshot, 'coverage_key' => 'order:'.$orderId, 'document_kind' => 'receipt'];
            if ($document) {
                $document->update($attributes);
            } else {
                $document = FiscalDocument::create(['id' => $localId] + $attributes);
            }
            $operation->update(['fiscal_document_id' => $document->id]);

            return $snapshot;
        }, fn ($result, $operation) => $this->apply($operation->fiscal_document_id, $result, true));

        return FiscalDocument::findOrFail($operation->fiscal_document_id);
    }

    public function issueBatch(string $batchId, string $storeId, string $register): FiscalDocument
    {
        $this->authorize();
        $store = $this->store($storeId);
        $operation = $this->runner->run('store:'.$store->id, 'issue', function (FiscalOperation $operation) use ($batchId, $store, $register) {
            $reference = \App\Models\CashPaymentBatch::findOrFail($batchId);
            $order = Order::lockForUpdate()->findOrFail($reference->order_id);
            $batch = \App\Models\CashPaymentBatch::lockForUpdate()->findOrFail($batchId);
            app(FiscalCoverageService::class)->assertBatchAvailable($batch);
            $document = FiscalDocument::where('coverage_key', 'batch:'.$batch->id)->lockForUpdate()->first();
            $this->setup->check(! $document || $document->document_kind === 'receipt' && in_array($document->status, ['draft', 'rejected']), __('Questo incasso ha già un documento o una richiesta da verificare.'));
            app(FiscalBatchAllocationService::class)->ensure($order);
            $context = $this->context($store, $register);
            if (app(FiscalWorkstationService::class)->defaults()['can_switch']) {
                app(FiscalWorkstationService::class)->remember($store->id, $register);
            }
            $localId = $document?->id ?? (string) Str::uuid();
            $snapshot = app(FiscalBatchAllocationService::class)->receipt($batch->fresh(), $this->setup->profile(), $context + ['local_id' => $localId]);
            $attributes = ['order_id' => $order->id, 'cash_payment_batch_id' => $batch->id, 'fiscal_store_id' => $store->id, 'user_id' => auth()->id(),
                'provider' => 'openapi', 'environment' => 'sandbox', 'fiscal_id' => $context['fiscal_id'], 'type' => 'sale', 'status' => 'submitting',
                'document_kind' => 'receipt', 'coverage_key' => 'batch:'.$batch->id, 'total_cents' => $batch->amount_cents, 'snapshot' => $snapshot];
            if ($document) {
                $document->update($attributes);
            } else {
                $document = FiscalDocument::create(['id' => $localId] + $attributes);
            }
            $operation->update(['fiscal_document_id' => $document->id]);

            return $snapshot;
        }, fn ($result, $operation) => $this->apply($operation->fiscal_document_id, $result, true));

        return FiscalDocument::findOrFail($operation->fiscal_document_id);
    }

    public function refresh(string $id, ?string $remoteId = null): void
    {
        $this->authorize();
        $document = $this->document($id);
        if ($document->document_kind === 'invoice') {
            app(FiscalInvoiceService::class)->refresh($id, $remoteId);

            return;
        }
        $remoteId = $document->remote_id ?? $remoteId;
        Validator::make(['id' => $remoteId], ['id' => 'required|string|regex:/^[a-zA-Z0-9_-]{1,100}$/'])->validate();
        $operation = FiscalOperation::where('fiscal_document_id', $id)->whereNotNull('active_key')->first();
        if ($operation) {
            $this->setup->check($this->recoverable($operation), __('Operazione ancora in corso. Attendi prima della verifica.'));
        }
        $result = $this->setup->read('receipt', ['id' => $remoteId]);
        $this->setup->check(($result['id'] ?? null) === $remoteId, __('Identificativo remoto incoerente.'));
        if ($document->remote_id === null) {
            $this->verifyRecovery($document, $result, $operation);
        }
        DB::transaction(function () use ($document, $result, $operation) {
            $this->apply($document->id, $result, $operation !== null);
            if ($operation) {
                $operation->update(['active_key' => null, 'status' => 'reconciled', 'response' => $result, 'error' => null]);
            }
        });
    }

    private function verifyRecovery(FiscalDocument $document, array $result, ?FiscalOperation $operation): void
    {
        $this->setup->check($operation !== null, __('Nessuna operazione incerta da riconciliare.'));
        if ($document->type === 'sale') {
            $this->setup->check(($result['additional_text'] ?? '') === $document->snapshot['reference'], __('Il documento remoto non contiene il riferimento univoco di questo conto.'));
            $expected = array_map(fn ($item) => [$item['id'], $item['quantity'] * 100, $item['unit_cents'], $item['vat_code']], $document->snapshot['items']);
            $actual = array_map(fn ($item) => [(int) $item['id'], $this->money($item['quantity']), $this->money($item['unit_price']), $item['vat_rate_code']], $result['items'] ?? []);
            $this->setup->check($expected === $actual, __('Le righe remote non coincidono con la richiesta originale.'));
            foreach (['cash' => 'cash_payment_amount', 'card' => 'electronic_payment_amount', 'voucher' => 'ticket_restaurant_payment_amount'] as $method => $field) {
                $this->setup->check(isset($result[$field]) && $this->money($result[$field]) === $document->snapshot['payments'][$method], 'I pagamenti remoti non coincidono con la richiesta originale.');
            }
        } else {
            $date = $result['document_date'] ?? null;
            $this->setup->check($date && Carbon::parse($date)->gte($operation->created_at->copy()->subSeconds(5)), __('Il documento remoto precede questa operazione.'));
            if ($document->type === 'return') {
                $expected = collect($document->snapshot['items'])->mapWithKeys(fn ($item) => [$item['id'] => $this->money($item['quantity'])])->sortKeys()->all();
                $actual = collect($result['items'] ?? [])->mapWithKeys(fn ($item) => [$item['id'] => $this->money($item['quantity'])])->sortKeys()->all();
                $this->setup->check($expected === $actual, __('Le quantità rese non coincidono con questa operazione.'));
            }
        }
    }

    private function apply(string $id, array $result, bool $newOperation = false): void
    {
        $document = FiscalDocument::lockForUpdate()->findOrFail($id);
        $store = FiscalStore::findOrFail($document->fiscal_store_id);
        $this->setup->check(filled($result['id'] ?? null) && ($result['fiscal_id'] ?? null) === $document->fiscal_id
            && ($result['store_id'] ?? null) === $store->remote_id && ($result['cr_id'] ?? null) === $document->snapshot['register_id']
            && ($result['type'] ?? null) === $document->type, __('La risposta non appartiene al documento atteso.'));
        $this->setup->check(! FiscalDocument::where('remote_id', $result['id'])->where('id', '!=', $document->id)->exists(), __('Documento remoto già associato a un’altra operazione.'));
        if ($document->parent_id) {
            $parent = FiscalDocument::findOrFail($document->parent_id);
            $this->setup->check(($result['parent_receipt_id'] ?? null) === $parent->remote_id, __('Documento fiscale collegato a un’altra vendita.'));
        }
        if ($document->type !== 'void') {
            $this->setup->check(isset($result['total_amount']) && $this->money($result['total_amount']) === $document->total_cents, __('Il totale del documento remoto non quadra.'));
        }
        $status = strtolower($result['status'] ?? '');
        $this->setup->check(in_array($status, ['sent', 'ready', 'voided'], true), __('Stato fiscale non conclusivo: verifica sul servizio.'));
        $issued = $status === 'sent' && filled($result['document_number'] ?? null);
        $document->update(['remote_id' => $result['id'], 'provider_status' => $status, 'document_number' => $result['document_number'] ?? null,
            'document_date' => $result['document_date'] ?? null, 'remote_data' => $result,
            'status' => $document->status === 'voided' || $status === 'voided' ? 'voided' : ($issued ? 'issued' : 'pending')]);
        if ($issued && $newOperation) {
            $store->update(['operational_status' => 'OPEN']);
        }
        if ($issued && $document->type === 'void') {
            FiscalDocument::whereKey($document->parent_id)->update(['status' => 'voided']);
        }
        Order::findOrFail($document->order_id)->events()->create(['user_id' => auth()->id(), 'action' => 'fiscal_updated',
            'details' => ['document' => $document->id, 'type' => $document->type, 'status' => $document->status]]);
    }

    public function returnable(string $id): array
    {
        $this->authorize();
        $document = $this->document($id);
        $this->setup->check($document->document_kind === 'receipt' && $document->type === 'sale' && $document->status === 'issued' && filled($document->remote_id), __('Vendita non disponibile per il reso.'));

        return $this->setup->read('returnable', ['id' => $document->remote_id])['items'];
    }

    public function reverse(string $id, string $type, array $items = []): FiscalDocument
    {
        $this->authorize();
        Validator::make(compact('type', 'items'), ['type' => 'required|in:return,void', 'items' => 'array|max:1000',
            'items.*.id' => 'required|integer|min:1|distinct', 'items.*.quantity' => 'required|integer|min:1|max:10000'])->validate();
        $parent = $this->document($id);
        $this->setup->check($parent->document_kind === 'receipt' && $parent->type === 'sale' && $parent->status === 'issued', __('Documento non disponibile per reso o annullamento.'));
        $this->refresh($id);
        $parent->refresh();
        $this->setup->check((bool) ($parent->remote_data[$type === 'return' ? 'is_returnable' : 'is_voidable'] ?? false), __('Operazione non consentita sul documento dal fornitore.'));
        $returnable = $type === 'return' ? collect($this->returnable($id))->keyBy('id') : collect();
        $store = $this->store($parent->fiscal_store_id);
        $operation = $this->runner->run('store:'.$store->id, $type, function (FiscalOperation $operation) use ($parent, $store, $type, $items, $returnable) {
            $current = FiscalDocument::lockForUpdate()->findOrFail($parent->id);
            $this->setup->check($current->status === 'issued', __('Lo stato del documento è cambiato.'));
            $this->setup->check(! FiscalDocument::where('parent_id', $parent->id)->whereNotIn('status', ['issued', 'rejected'])->exists(), __('Una rettifica è ancora da verificare.'));
            $context = $this->context($store, $current->snapshot['register_id']);
            $total = 0;
            $requested = [];
            if ($type === 'return') {
                $this->setup->check(count($items) > 0, __('Seleziona almeno un articolo da rendere.'));
                foreach ($items as $item) {
                    $available = $returnable->get($item['id']);
                    $original = collect($current->snapshot['items'])->firstWhere('id', $item['id']);
                    $this->setup->check($available && $original && $this->money($available['quantity']) - $this->money($available['returned_quantity']) >= $item['quantity'] * 100, __('Quantità resa non disponibile.'));
                    // Also account for local returns completed after the availability GET.
                    $returned = FiscalDocument::where('parent_id', $current->id)->where('type', 'return')->where('status', 'issued')->get()
                        ->sum(fn ($child) => collect($child->snapshot['items'])->where('id', $item['id'])->sum(fn ($row) => $this->money($row['quantity'])));
                    $this->setup->check($returned + $item['quantity'] * 100 <= $original['quantity'] * 100, __('Quantità già resa da un’altra operazione.'));
                    $total += $original['unit_cents'] * $item['quantity'];
                    $requested[] = ['id' => $item['id'], 'quantity' => FiscalMoney::decimal($item['quantity'] * 100)];
                }
            }
            $snapshot = $context + ['id' => $current->remote_id, 'items' => $requested];
            $document = FiscalDocument::create(['order_id' => $current->order_id, 'parent_id' => $current->id, 'fiscal_store_id' => $store->id,
                'user_id' => auth()->id(), 'provider' => $current->provider, 'environment' => $current->environment, 'fiscal_id' => $current->fiscal_id,
                'type' => $type, 'status' => 'submitting', 'total_cents' => $type === 'void' ? $current->total_cents : $total, 'snapshot' => $snapshot]);
            $operation->update(['fiscal_document_id' => $document->id]);

            return $snapshot;
        }, fn ($result, $operation) => $this->apply($operation->fiscal_document_id, $result, true));

        return FiscalDocument::findOrFail($operation->fiscal_document_id);
    }

    public function close(string $storeId): void
    {
        $this->authorize();
        $store = $this->store($storeId);
        app(FiscalWorkstationService::class)->assertSelection($store->id);
        $this->runner->run('store:'.$store->id, 'close', function () use ($store) {
            $current = $store->fresh();
            $this->setup->check($current->active && $current->status === 'ONLINE', __('Sede non operativa.'));
            $this->setup->check($current->operational_status !== 'CLOSED', __('Sessione fiscale già chiusa. Sincronizza lo stato se il fornitore l’ha riaperta.'));
            $this->setup->check(! FiscalDocument::where('fiscal_store_id', $store->id)->where('document_kind', 'receipt')->whereIn('status', ['submitting', 'unknown', 'pending'])->exists(), __('Verifica i documenti ancora in sospeso prima della chiusura fiscale.'));

            return ['id' => $store->remote_id];
        }, function ($result) use ($store) {
            $this->setup->check(is_string($result['event_id'] ?? null) && preg_match('/^[a-zA-Z0-9_-]{1,100}$/', $result['event_id']), __('Chiusura senza identificativo evento: verifica l’esito.'));
            $store->update(['last_closed_at' => now(), 'operational_status' => 'CLOSED']);
        });
    }

    public function storeEvents(string $storeId): array
    {
        $this->authorize();
        $store = $this->store($storeId);

        return $this->setup->read('store_events', ['store_id' => $store->remote_id, 'fiscal_id' => $this->setup->profile()->fiscal_id])['events'];
    }

    public function reconcileClose(string $operationId, string $eventId): void
    {
        $this->setup->admin();
        $operation = FiscalOperation::where('action', 'close')->whereNotNull('active_key')->findOrFail($operationId);
        $this->setup->check($this->recoverable($operation), __('Chiusura ancora in corso.'));
        $store = $this->store(Str::after($operation->resource_key, 'store:'));
        $event = collect($this->storeEvents($store->id))->firstWhere('id', $eventId);
        $this->setup->check($event && $event['action'] === 'close' && $event['store_id'] === $store->remote_id
            && $event['fiscal_id'] === $this->setup->profile()->fiscal_id && Carbon::parse($event['created_at'])->gte($operation->created_at->copy()->subSeconds(5)), __('Evento di chiusura non corrispondente all’operazione.'));
        DB::transaction(function () use ($operation, $store, $event) {
            $store->update(['last_closed_at' => Carbon::parse($event['created_at']), 'operational_status' => 'CLOSED']);
            $operation->update(['active_key' => null, 'status' => 'reconciled', 'response' => ['event_id' => $event['id']], 'error' => null]);
        });
    }

    public function remoteDocuments(string $storeId, int $skip = 0): array
    {
        $this->authorize();
        Validator::make(compact('skip'), ['skip' => 'integer|min:0|max:1000000'])->validate();
        $store = $this->store($storeId);

        return $this->setup->read('receipts', ['fiscal_id' => $this->setup->profile()->fiscal_id, 'store_id' => $store->remote_id, 'skip' => $skip])['documents'];
    }

    public function pdf(string $id): string
    {
        $this->authorize();
        $document = $this->document($id);
        if ($document->document_kind === 'invoice') {
            return $this->setup->read('invoice_pdf', ['id' => $document->remote_id])['pdf'];
        }
        $this->setup->check(filled($document->remote_id), __('Documento non ancora disponibile.'));

        return $this->setup->read('receipt_pdf', ['id' => $document->remote_id])['pdf'];
    }

    public function document(string $id): FiscalDocument
    {
        return FiscalDocument::where('fiscal_id', $this->setup->profile()->fiscal_id)->where('environment', 'sandbox')->findOrFail($id);
    }

    private function recoverable(FiscalOperation $operation): bool
    {
        return $operation->status === 'unknown' || $operation->created_at->lt(now()->subSeconds((int) config('fiscal.openapi.timeout') + 15));
    }

    private function money(mixed $value): int
    {
        $this->setup->check(is_numeric($value) && (float) $value >= 0, __('Importo fiscale non valido.'));

        return OrderService::cents(number_format((float) $value, 2, '.', ''));
    }
}

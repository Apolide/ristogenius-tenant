<?php

namespace App\Services\Fiscal;

use App\Models\CashPaymentBatch;
use App\Models\FiscalCopyRequest;
use App\Models\FiscalDocument;
use App\Models\MessageOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class FiscalCopyService
{
    public function validate(string $email, bool $agreed, ?string $customerId = null): void
    {
        Validator::make(compact('email', 'agreed', 'customerId'), ['email' => 'required|email|max:254', 'agreed' => 'accepted', 'customerId' => 'nullable|uuid|exists:customers,id'])->validate();
    }

    public function request(string $batchId, string $email, bool $agreed, ?string $customerId = null): FiscalCopyRequest
    {
        app(FiscalDocumentService::class)->authorize();
        $email = trim($email);
        $this->validate($email, $agreed, $customerId);

        return DB::transaction(function () use ($batchId, $email, $customerId) {
            CashPaymentBatch::lockForUpdate()->findOrFail($batchId);
            $existing = FiscalCopyRequest::where('cash_payment_batch_id', $batchId)->first();
            if ($existing) {
                app(FiscalSetupService::class)->check($existing->email === $email, __('Esiste già una richiesta per un altro recapito: verifica il destinatario registrato.'));

                return $existing;
            }

            return FiscalCopyRequest::create(['cash_payment_batch_id' => $batchId, 'customer_id' => $customerId, 'user_id' => auth()->id(), 'email' => $email, 'agreed_at' => now()]);
        });
    }

    /** Queues an agreed copy only after issuance. Does not send mail or issue documents. */
    public function queueReady(int $limit = 50, ?string $batchId = null): int
    {
        $count = 0;
        $ids = FiscalCopyRequest::whereNull('message_outbox_id')->when($batchId, fn ($q) => $q->where('cash_payment_batch_id', $batchId))->whereExists(function ($q) {
            $q->selectRaw('1')->from('fiscal_documents')->whereColumn('fiscal_documents.cash_payment_batch_id', 'fiscal_copy_requests.cash_payment_batch_id')->whereNull('parent_id')->where('status', 'issued')->whereNotNull('remote_id');
        })->oldest()->limit(max(1, min(100, $limit)))->pluck('id');
        foreach ($ids as $id) {
            $count += DB::transaction(function () use ($id) {
                $request = FiscalCopyRequest::lockForUpdate()->findOrFail($id);
                if ($request->message_outbox_id) {
                    return 0;
                }
                $doc = FiscalDocument::where('cash_payment_batch_id', $request->cash_payment_batch_id)->whereNull('parent_id')->where('status', 'issued')->whereNotNull('remote_id')->first();
                if (! $doc) {
                    return 0;
                }
                $url = URL::temporarySignedRoute('fiscal-copy.download', now()->addDays(7), ['copy' => $request->id, 'document' => $doc->id]);
                $language = \App\Models\Customer::find($request->customer_id)?->lang
                    ?: ($doc->order->customer_language ?: ($doc->order->customer?->lang ?? 'it'));
                $language = array_key_exists($language, (array) config('tenant.backend_languages', [])) ? $language : 'it';
                $label = $doc->document_kind === 'invoice' ? __('Copia di cortesia fattura sandbox', [], $language) : __('Documento commerciale sandbox', [], $language);
                $outbox = MessageOutbox::firstOrCreate(['deduplication_key' => 'fiscal-copy:'.$request->id], ['event_type' => 'fiscal_copy', 'aggregate_type' => 'fiscal_copy', 'aggregate_id' => $request->id, 'status' => 'pending', 'attempts' => 0, 'available_at' => now(),
                    'payload' => ['message_case' => 'fiscal_copy', 'deliveries' => [['recipient' => ['email' => $request->email], 'language' => $language, 'name' => '', 'subject' => $label,
                        'message' => __('Come richiesto, puoi scaricare la copia PDF dal collegamento valido per 7 giorni. Documento di prova sandbox: non certifica incassi reali.', [], $language).($doc->document_kind === 'invoice' ? __(' La copia di cortesia non sostituisce la fattura elettronica trasmessa tramite SDI.', [], $language) : ''), 'url' => $url]]]]);
                $request->update(['message_outbox_id' => $outbox->id, 'queued_at' => now()]);

                return 1;
            });
        }

        return $count;
    }
}

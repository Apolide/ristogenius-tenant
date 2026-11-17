<?php

namespace App\Services\Fiscal;

use App\Models\CashPaymentBatch;
use App\Models\CashRefund;
use App\Models\FiscalDocument;
use Illuminate\Validation\ValidationException;

class FiscalCoverageService
{
    public function assertBatchAvailable(CashPaymentBatch $batch): void
    {
        $whole = FiscalDocument::where('order_id', $batch->order_id)->whereNotNull('sale_order_id')->whereNotIn('status', ['draft', 'rejected'])->exists();
        if ($whole) {
            throw ValidationException::withMessages(['fiscal' => __('Questo incasso è già coperto dal documento dell’intero conto: non emettere un secondo documento.')]);
        }
        if (CashRefund::where('cash_payment_batch_id', $batch->id)->exists()) {
            throw ValidationException::withMessages(['fiscal' => __('Incasso con rimborsi registrati: verifica la rettifica fiscale prima di una nuova emissione.')]);
        }
    }

    public function assertWholeAvailable(string $orderId): void
    {
        if (CashPaymentBatch::where('order_id', $orderId)->exists()) {
            throw ValidationException::withMessages(['fiscal' => __('Emetti il documento di ciascun incasso dalla schermata del conto.')]);
        }
        if (FiscalDocument::where('order_id', $orderId)->whereNotNull('cash_payment_batch_id')->whereNull('parent_id')->whereNotIn('status', ['draft', 'rejected'])->exists()) {
            throw ValidationException::withMessages(['fiscal' => __('Esistono documenti per singoli incassi: l’intero conto non può essere certificato di nuovo.')]);
        }
    }
}

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
            throw ValidationException::withMessages(['fiscal' => __('fiscal.messages.payment_covered_by_whole_bill_document')]);
        }
        if (CashRefund::where('cash_payment_batch_id', $batch->id)->exists()) {
            throw ValidationException::withMessages(['fiscal' => __('fiscal.messages.fiscal_adjustment_required_after_refund')]);
        }
    }

    public function assertWholeAvailable(string $orderId): void
    {
        if (CashPaymentBatch::where('order_id', $orderId)->exists()) {
            throw ValidationException::withMessages(['fiscal' => __('fiscal.messages.issue_each_payment_document_from_the_bill_screen')]);
        }
        if (FiscalDocument::where('order_id', $orderId)->whereNotNull('cash_payment_batch_id')->whereNull('parent_id')->whereNotIn('status', ['draft', 'rejected'])->exists()) {
            throw ValidationException::withMessages(['fiscal' => __('fiscal.messages.payment_documents_block_whole_bill_document')]);
        }
    }
}

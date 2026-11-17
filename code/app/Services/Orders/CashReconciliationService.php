<?php

namespace App\Services\Orders;

use App\Models\CashPaymentBatch;
use App\Models\CashRefund;
use App\Models\FiscalDocument;
use App\Models\OrderPayment;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class CashReconciliationService
{
    public function report(string $from, string $to): array
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('from', 'to'), ['from' => 'required|date_format:Y-m-d', 'to' => 'required|date_format:Y-m-d|after_or_equal:from'])->validate();
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();
        $payments = OrderPayment::whereBetween('created_at', [$start, $end])->get();
        $refunds = CashRefund::whereBetween('created_at', [$start, $end])->get();
        $batches = CashPaymentBatch::whereBetween('created_at', [$start, $end])->get();
        $documents = FiscalDocument::whereBetween('created_at', [$start, $end])->get();
        $coverage = FiscalDocument::whereIn('order_id', $batches->pluck('order_id'))->whereNull('parent_id')->get();
        $totals = [];
        foreach (['cash', 'card', 'voucher', 'manual'] as $method) {
            $rows = $payments->where('method', $method);
            $totals[$method] = ['paid' => (int) $rows->sum('amount_cents'), 'tips' => (int) $rows->sum('tip_cents'), 'refunded' => (int) $refunds->where('method', $method)->sum('amount_cents')];
        }
        $fiscalTotals = [];
        foreach (['receipt', 'invoice'] as $kind) {
            $final = $documents->where('document_kind', $kind)->whereIn('status', ['issued', 'voided']);
            $fiscalTotals[$kind] = ['sales' => (int) $final->where('type', 'sale')->sum('total_cents'), 'corrections' => (int) $final->where('type', '!=', 'sale')->sum('total_cents')];
        }
        $issues = [];
        foreach ($batches as $batch) {
            $doc = $coverage->first(fn ($d) => $d->cash_payment_batch_id === $batch->id || $d->sale_order_id === $batch->order_id);
            if (! $doc || ! in_array($doc->status, ['issued', 'voided'])) {
                $issues[] = ['order' => $batch->order_id, 'batch' => $batch->id, 'amount' => $batch->amount_cents, 'status' => $doc?->status ?? 'senza_documento'];
            }
        }

        // Corrections and refunds are intentionally separate: money is not moved by fiscal writes.
        return compact('totals', 'fiscalTotals', 'issues', 'documents', 'refunds', 'payments');
    }

    public function csv(array $report): string
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        $write = function (array $row) use ($stream) {
            $row = array_map(fn ($v) => preg_match('/^[\s]*[=+@-]/u', (string) $v) ? "'".$v : $v, $row);
            fputcsv($stream, $row, ';', '"', '');
        };
        $write([__('cash_desk.type'), 'ID', __('cash_desk.date'), __('cash_desk.messages.order_payment'), __('cash_desk.messages.method_status'), __('cash_desk.messages.amount_eur'), __('cash_desk.messages.tip_eur'), __('cash_desk.reference')]);
        foreach ($report['payments'] as $p) {
            $write([__('cash_desk.payment'), $p->id, $p->created_at->toDateTimeString(), $p->order_id, $p->method, $this->decimal($p->amount_cents), $this->decimal($p->tip_cents), $p->payer]);
        }
        foreach ($report['refunds'] as $r) {
            $write([__('cash_desk.messages.refund'), $r->id, $r->created_at->toDateTimeString(), $r->cash_payment_batch_id, $r->method, $this->decimal($r->amount_cents), '0,00', $r->external_reference]);
        }
        foreach ($report['documents'] as $d) {
            $write([$d->document_kind.':'.$d->type, $d->id, $d->created_at->toDateTimeString(), $d->order_id, $d->status, $this->decimal($d->total_cents), '0,00', $d->document_number]);
        }
        rewind($stream);
        $result = stream_get_contents($stream);
        fclose($stream);

        return $result;
    }

    private function decimal(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '');
    }
}

<?php

namespace App\Services\Orders;

use App\Models\CashPaymentBatch;
use App\Models\CashRefund;
use App\Models\CashShift;
use App\Models\Order;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CashRefundService
{
    /** Records money already returned outside this application. Never calls a PSP. */
    public function record(string $batchId, string $key, int $amount, string $method, string $reason, string $reference = ''): CashRefund
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        $reason = trim($reason);
        $reference = trim($reference);
        Validator::make(compact('key', 'amount', 'method', 'reason', 'reference'), ['key' => 'required|uuid', 'amount' => 'integer|min:1|max:100000000',
            'method' => 'required|in:cash,card,voucher', 'reason' => 'required|string|max:255', 'reference' => 'string|max:255'])->validate();

        return DB::transaction(function () use ($batchId, $key, $amount, $method, $reason, $reference) {
            $batch = CashPaymentBatch::findOrFail($batchId);
            Order::lockForUpdate()->findOrFail($batch->order_id);
            $batch = CashPaymentBatch::lockForUpdate()->findOrFail($batchId);
            $hash = hash('sha256', json_encode([$batchId, $amount, $method, $reason, $reference]));
            if ($existing = CashRefund::find($key)) {
                if ($existing->request_hash !== $hash) {
                    throw ValidationException::withMessages(['refund' => 'Richiesta rimborso già usata con dati diversi.']);
                }

                return $existing;
            }
            $paid = $batch->amount_cents + $batch->tip_cents;
            if ((int) CashRefund::where('cash_payment_batch_id', $batchId)->sum('amount_cents') + $amount > $paid) {
                throw ValidationException::withMessages(['refund' => 'Il rimborso supera l’incasso, incluse le mance.']);
            }
            $shift = CashShift::where('active_key', 'main')->lockForUpdate()->first();
            $refund = CashRefund::create(['id' => $key, 'cash_payment_batch_id' => $batchId, 'cash_shift_id' => $shift?->id, 'user_id' => auth()->id(),
                'amount_cents' => $amount, 'method' => $method, 'reason' => $reason, 'external_reference' => $reference ?: null, 'request_hash' => $hash]);
            $batch->order->events()->create(['user_id' => auth()->id(), 'action' => 'cash_refund_recorded', 'details' => ['refund' => $key, 'amount_cents' => $amount, 'method' => $method]]);

            return $refund;
        });
    }
}

<?php

namespace App\Services\Orders;

use App\Models\CashPaymentBatch;
use App\Models\CashShift;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Services\Personnel\PersonnelPermissionsService as Permissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CashCheckoutService
{
    public function pay(string $orderId, string $key, array $amounts, int $expectedTotal, int $expectedBalance, ?string $shareId = null, ?int $planVersion = null,
        string $payer = '', int $tip = 0, string $tipMethod = 'cash', string $voucherIssuer = '', string $voucherReference = '', int $voucherQuantity = 0): CashPaymentBatch
    {
        app(OrderService::class)->authorize(Permissions::CASH_REGISTER);
        Validator::make(compact('key', 'amounts', 'payer', 'tip', 'tipMethod', 'voucherIssuer', 'voucherReference', 'voucherQuantity'), [
            'key' => 'required|uuid', 'amounts' => 'required|array:cash,card,voucher', 'amounts.cash' => 'required|integer|min:0|max:100000000',
            'amounts.card' => 'required|integer|min:0|max:100000000', 'amounts.voucher' => 'required|integer|min:0|max:100000000',
            'payer' => 'string|max:100', 'tip' => 'integer|min:0|max:10000000', 'tipMethod' => 'in:cash,card',
            'voucherIssuer' => 'string|max:100', 'voucherReference' => 'string|max:100', 'voucherQuantity' => 'integer|min:0|max:10000',
        ])->validate();
        $amounts = ['cash' => $amounts['cash'], 'card' => $amounts['card'], 'voucher' => $amounts['voucher']];
        $this->check($amounts['voucher'] === 0 || filled($voucherIssuer) && $voucherQuantity > 0, 'Indica emittente e numero dei buoni pasto.');
        $this->check($amounts['voucher'] > 0 || $voucherQuantity === 0, 'Non sono presenti importi in buoni pasto.');
        $hash = hash('sha256', json_encode([$orderId, $shareId, $amounts, $payer, $tip, $tipMethod, $voucherIssuer, $voucherReference, $voucherQuantity], JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($orderId, $key, $amounts, $expectedTotal, $expectedBalance, $shareId, $planVersion, $payer, $tip, $tipMethod, $voucherIssuer, $voucherReference, $voucherQuantity, $hash) {
            $order = Order::lockForUpdate()->findOrFail($orderId);
            $existing = CashPaymentBatch::find($key);
            if ($existing) {
                $this->check($existing->order_id === $orderId && hash_equals($existing->request_hash, $hash), 'Identificativo incasso già utilizzato per un’altra operazione.');

                return $existing;
            }
            $this->check(! OrderPayment::where('idempotency_key', $key)->exists(), 'Identificativo già usato da un pagamento precedente.');
            app(OrderService::class)->assertPayable($order, $expectedTotal, $expectedBalance);
            $plan = $order->billPlan()->first();
            $share = null;
            if ($plan) {
                app(CashBillService::class)->assertCurrent($order, $plan, $planVersion);
                $this->check($shareId !== null, 'Seleziona una delle quote del conto.');
                $share = $plan->shares()->findOrFail($shareId);
                $this->check(! $share->batches()->exists(), 'Questa quota è già stata incassata.');
            } else {
                $this->check($shareId === null, 'La divisione del conto non è più disponibile.');
            }
            $target = $share ? $share->balanceCents() : $order->balanceCents();
            $paid = array_sum($amounts);
            $this->check($paid <= $target && ($share === null || $paid === $target) && ($paid > 0 || $order->totalCents() === 0), 'La somma di contanti, carta e buoni deve corrispondere al residuo del pagante.');
            $this->check($paid <= $order->balanceCents(), 'Importo superiore al residuo del conto.');
            $shift = CashShift::where('active_key', 'main')->lockForUpdate()->first();
            $batch = CashPaymentBatch::create(['id' => $key, 'order_id' => $orderId, 'cash_bill_share_id' => $share?->id, 'user_id' => auth()->id(),
                'request_hash' => $hash, 'amount_cents' => $paid, 'tip_cents' => $tip]);
            $order->update(['cash_snapshot' => app(CashTotalsService::class)->snapshot($order)]);
            foreach ($amounts as $method => $amount) {
                $methodTip = $method === $tipMethod ? $tip : 0;
                if ($amount === 0 && $methodTip === 0) {
                    continue;
                }
                $order->payments()->create(['cash_payment_batch_id' => $batch->id, 'user_id' => auth()->id(), 'amount_cents' => $amount, 'method' => $method,
                    'cash_shift_id' => $shift?->id, 'tip_cents' => $methodTip, 'payer' => $payer ?: $share?->label,
                    'voucher_issuer' => $method === 'voucher' ? $voucherIssuer : null, 'voucher_reference' => $method === 'voucher' ? $voucherReference : null,
                    'voucher_quantity' => $method === 'voucher' ? $voucherQuantity : 0]);
            }
            if ($share && filled($payer)) {
                $share->update(['label' => $payer]);
            }
            $plan?->increment('version');
            app(\App\Services\Fiscal\FiscalBatchAllocationService::class)->ensure($order);
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'payment_recorded',
                'details' => ['amount_cents' => $paid, 'method' => count(array_filter($amounts)) > 1 ? 'mixed' : array_key_first(array_filter($amounts)), 'payer' => $payer ?: $share?->label, 'batch' => $batch->id]]);
            if ($paid === $expectedBalance) {
                $order->update(['status' => 'paid', 'paid_at' => now()]);
                DB::table('order_tables')->where('order_id', $orderId)->update(['active_table_id' => null]);
                $order->events()->create(['user_id' => auth()->id(), 'action' => 'paid', 'details' => ['amount_cents' => $expectedTotal]]);
                if ($order->customer_email) {
                    app(\App\Services\PublicOrders\OrderNotifications::class)->status($order, 'paid');
                }
            }

            return $batch->fresh();
        });
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}

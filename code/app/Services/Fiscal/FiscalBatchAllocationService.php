<?php

namespace App\Services\Fiscal;

use App\Models\CashBillPlan;
use App\Models\CashBillShare;
use App\Models\CashPaymentBatch;
use App\Models\Order;
use App\Services\Orders\CashBillPricingService;
use Illuminate\Validation\ValidationException;

class FiscalBatchAllocationService
{
    /** Caller must hold the order lock. Allocation covers payments, never tips. */
    public function ensure(Order $order): void
    {
        $pricing = app(CashBillPricingService::class)->snapshot($order);
        $remaining = collect($pricing['taxes'])->mapWithKeys(fn ($tax) => [(int) $tax['rate'] => (int) $tax['gross_cents']])->all();
        $legacy = (int) $order->payments()->whereNull('cash_payment_batch_id')->sum('amount_cents');
        if ($legacy > 0) {
            foreach ($this->allocate($legacy, $remaining) as $rate => $amount) {
                $remaining[$rate] -= $amount;
            }
        }
        $batches = CashPaymentBatch::where('order_id', $order->id)->orderBy('created_at')->orderBy('id')->get();
        foreach ($batches->filter(fn ($batch) => $batch->fiscal_snapshot !== null) as $batch) {
            foreach ($batch->fiscal_snapshot['groups'] as $rate => $amount) {
                $this->check(isset($remaining[$rate]) && $amount <= $remaining[$rate], __('fiscal.messages.saved_fiscal_allocation_is_inconsistent'));
                $remaining[$rate] -= $amount;
            }
        }
        foreach ($batches->filter(fn ($batch) => $batch->fiscal_snapshot === null) as $batch) {

            $share = $batch->cash_bill_share_id ? CashBillShare::findOrFail($batch->cash_bill_share_id) : null;
            $plan = $share ? CashBillPlan::findOrFail($share->cash_bill_plan_id) : null;
            $items = [];
            if ($plan?->mode === 'items') {
                $groups = [];
                foreach (collect($plan->pricing['units'])->whereIn('id', $share->units) as $unit) {
                    $rate = (int) $unit['rate'];
                    $groups[$rate] = ($groups[$rate] ?? 0) + $unit['amount_cents'];
                    $items[] = ['quantity' => 1, 'unit_cents' => (int) $unit['amount_cents'], 'rate' => $rate, 'description' => $unit['name'],
                        'type' => $order->fulfillment === 'dine_in' || in_array($unit['line_id'], ['covers', 'service', 'delivery']) ? 'service' : 'goods'];
                }
            } else {
                $groups = $this->allocate($batch->amount_cents, $remaining);
                foreach ($groups as $rate => $amount) {
                    if ($amount > 0) {
                        $names = collect($pricing['units'])->where('rate', $rate)->pluck('name')->unique()->implode(', ');
                        $items[] = ['quantity' => 1, 'unit_cents' => $amount, 'rate' => (int) $rate,
                            'description' => mb_substr(__('fiscal.document_text.consumption_share_prefix', [], 'it').$names, 0, 950), 'type' => 'service'];
                    }
                }
            }
            $payments = ['cash' => 0, 'card' => 0, 'voucher' => 0];
            $quantity = 0;
            foreach ($batch->payments()->get() as $payment) {
                $this->check(isset($payments[$payment->method]), __('fiscal.messages.payment_method_not_classified_for_this_payment'));
                $payments[$payment->method] += (int) $payment->amount_cents;
                $quantity += (int) $payment->voucher_quantity;
            }
            $this->check(array_sum($payments) === $batch->amount_cents && array_sum($groups) === $batch->amount_cents, __('fiscal.messages.payment_and_fiscal_allocation_do_not_balance'));
            $batch->update(['fiscal_snapshot' => ['groups' => $groups, 'items' => $items, 'payments' => $payments, 'voucher_count' => $quantity,
                'total_cents' => $batch->amount_cents, 'payer' => $batch->payments()->value('payer'), 'batch_id' => $batch->id]]);

            foreach ($groups as $rate => $amount) {
                $this->check(isset($remaining[$rate]) && $amount <= $remaining[$rate], __('fiscal.messages.fiscal_allocation_exceeds_the_bill_balance'));
                $remaining[$rate] -= $amount;
            }
        }
    }

    public function receipt(CashPaymentBatch $batch, \App\Models\FiscalProfile $profile, array $context): array
    {
        $snapshot = $batch->fiscal_snapshot;
        $this->check($snapshot !== null && $batch->amount_cents > 0, __('fiscal.messages.payment_has_no_issuable_fiscal_amount'));
        $payments = $snapshot['payments'];
        $this->check($payments['cash'] !== array_sum($payments) || $payments['cash'] % 5 === 0, __('fiscal.messages.cash_payment_rounding_required'));
        $items = [];
        foreach ($snapshot['items'] as $item) {
            $code = $item['rate'] === 0 ? $profile->zero_vat_code : number_format($item['rate'], 2, '.', '');
            $this->check(in_array($code, ['2.00', '4.00', '5.00', '10.00', '22.00', 'N1', 'N2', 'N3', 'N4', 'N5', 'N6'], true), __('fiscal.messages.vat_category_or_rate_not_configured'));
            $items[] = ['id' => count($items) + 1, 'quantity' => $item['quantity'], 'unit_cents' => $item['unit_cents'], 'description' => $item['description'], 'type' => $item['type'], 'vat_code' => $code];
        }

        return $context + ['items' => $items, 'payments' => $payments, 'voucher_count' => $snapshot['voucher_count'], 'total_cents' => $batch->amount_cents,
            'reference' => 'RistoPilot '.$context['local_id'].__('fiscal.document_text.payment_reference_suffix', [], 'it').$batch->id];
    }

    private function allocate(int $amount, array $weights): array
    {
        $total = array_sum($weights);
        $this->check($amount >= 0 && $amount <= $total, __('fiscal.messages.fiscal_amount_exceeds_available_balance'));
        $result = $rest = [];
        foreach ($weights as $rate => $weight) {
            $result[$rate] = $total ? intdiv($amount * $weight, $total) : 0;
            $rest[$rate] = $total ? ($amount * $weight) % $total : 0;
        }
        arsort($rest, SORT_NUMERIC);
        $left = $amount - array_sum($result);
        foreach ($rest as $rate => $value) {
            if ($left-- > 0) {
                $result[$rate]++;
            }
        }

        return $result;
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['fiscal' => $message]);
        }
    }
}

<?php

namespace App\Services\Fiscal;

use App\Models\FiscalProfile;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class FiscalReceiptBuilder
{
    public function build(Order $order, FiscalProfile $profile, array $context, string $manualMethod, int $voucherCount): array
    {
        $this->check($order->status === 'paid' && $order->balanceCents() === 0, __('fiscal.messages.issue_the_document_only_after_full_payment_of_the_bill'));
        $this->check($order->cash_snapshot !== null, __('fiscal.messages.legacy_bill_missing_fiscal_snapshot'));
        $this->check($order->totalCents() > 0, 'I conti omaggio richiedono una gestione fiscale dedicata e non sono emettibili in questa versione.');
        $payments = ['cash' => 0, 'card' => 0, 'voucher' => 0];
        foreach ($order->payments()->get() as $payment) {
            $method = $payment->method === 'manual' ? $manualMethod : $payment->method;
            $this->check(isset($payments[$method]), __('fiscal.messages.manual_payment_method_required'));
            $payments[$method] += (int) $payment->amount_cents;
        }
        $this->check(array_sum($payments) === $order->totalCents(), __('fiscal.messages.payments_do_not_match_the_bill'));
        $this->check($payments['voucher'] === 0 || $voucherCount > 0, __('fiscal.messages.enter_the_actual_number_of_meal_vouchers'));
        $this->check($payments['voucher'] > 0 || $voucherCount === 0, __('fiscal.messages.the_bill_contains_no_meal_voucher_payments'));
        $this->check($payments['cash'] !== array_sum($payments) || $payments['cash'] % 5 === 0, __('fiscal.messages.cash_only_bill_rounding_required'));
        $lines = [];
        foreach ($order->items()->whereNull('order_courses.rejected_at')->orderBy('order_items.id')->get() as $item) {
            $lines[] = ['id' => $item->id, 'quantity' => (int) $item->quantity, 'gross' => $item->quantity * $item->unit_price_cents, 'rate' => (int) $item->vat,
                'description' => mb_substr($item->product_name, 0, 950), 'type' => $order->fulfillment === 'dine_in' ? 'service' : 'goods'];
        }
        foreach (['Coperti' => $order->covers * $order->cover_price_cents, 'Consegna' => (int) $order->delivery_fee_cents, 'Servizio' => $order->serviceFeeCents()] as $label => $gross) {
            if ($gross > 0) {
                $lines[] = ['quantity' => 1, 'gross' => $gross, 'rate' => (int) $order->auxiliary_vat, 'id' => ['Coperti' => 'covers', 'Consegna' => 'delivery', 'Servizio' => 'service'][$label], 'description' => $label, 'type' => 'service'];
            }
        }
        $this->check(array_sum(array_column($lines, 'gross')) === (int) $order->cash_snapshot['gross_cents'], __('fiscal.messages.bill_lines_do_not_match_the_saved_snapshot'));
        $items = [];
        foreach ($order->cash_snapshot['taxes'] as $tax) {
            $group = array_filter($lines, fn ($line) => $line['rate'] === $tax['rate']);
            $weights = array_map(fn ($line) => $line['gross'], $group);
            $allocations = $this->allocate(array_sum($weights) - $tax['gross_cents'], $weights);
            foreach ($group as $key => $line) {
                $net = $line['gross'] - ($order->cash_snapshot['line_discounts'][$line['id']] ?? $allocations[$key]);
                $unit = intdiv($net, $line['quantity']);
                $extra = $net % $line['quantity'];
                $code = $line['rate'] === 0 ? $profile->zero_vat_code : number_format($line['rate'], 2, '.', '');
                $this->check(in_array($code, ['2.00', '4.00', '5.00', '10.00', '22.00', 'N1', 'N2', 'N3', 'N4', 'N5', 'N6'], true), __('fiscal.messages.unsupported_vat_configuration'));
                // At most two rows per original product. Discounted unit prices are
                // integer cents; never use the provider's ambiguous global discount.
                foreach ([$unit => $line['quantity'] - $extra, $unit + 1 => $extra] as $price => $quantity) {
                    if ($quantity > 0) {
                        $items[] = ['id' => count($items) + 1, 'quantity' => $quantity, 'unit_cents' => $price, 'description' => $line['description'], 'vat_code' => $code, 'type' => $line['type']];
                    }
                }
            }
        }
        $this->check(count($items) > 0 && count($items) <= 1000, __('fiscal.messages.invalid_number_of_fiscal_lines'));
        $this->check(array_sum(array_map(fn ($item) => $item['quantity'] * $item['unit_cents'], $items)) === $order->totalCents(), __('fiscal.messages.fiscal_line_totals_do_not_balance'));

        return $context + ['items' => $items, 'payments' => $payments, 'voucher_count' => $voucherCount, 'total_cents' => $order->totalCents(),
            'reference' => 'RistoPilot '.$context['local_id'].($order->effectiveDiscountCents() ? __('fiscal.document_text.discount_reference_suffix', [], 'it').FiscalMoney::decimal($order->effectiveDiscountCents()) : '')];
    }

    private function allocate(int $discount, array $weights): array
    {
        $sum = array_sum($weights);
        $result = $remainders = [];
        foreach ($weights as $key => $weight) {
            $result[$key] = $sum ? intdiv($discount * $weight, $sum) : 0;
            $remainders[$key] = $sum ? ($discount * $weight) % $sum : 0;
        }
        arsort($remainders, SORT_NUMERIC);
        $remaining = $discount - array_sum($result);
        foreach ($remainders as $key => $remainder) {
            if ($remaining-- > 0) {
                $result[$key]++;
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

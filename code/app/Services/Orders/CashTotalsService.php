<?php

namespace App\Services\Orders;

use App\Models\Order;
use App\Services\Fidelity\FidelityCheckout;

class CashTotalsService
{
    // All values are integer cents. Discounts use largest remainders so VAT groups
    // reconcile exactly even when a cent cannot be divided proportionally.
    public function snapshot(Order $order): array
    {
        if ($order->cash_snapshot !== null) {
            return $order->cash_snapshot;
        }
        $groups = [];
        $lines = $this->lines($order);
        $coupon = app(\App\Services\Marketing\CouponPricing::class)->discounts($order, $lines);
        $netWeights = [];
        foreach ($lines as $line) {
            $netWeights[$line['id']] = $line['gross_cents'] - ($coupon[$line['id']] ?? 0);
        }
        // No implicit stacking with manual discounts: the staff must remove the coupon first.
        if (array_sum($coupon) > 0 && $order->discount_cents > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages(['order' => __('marketing.not_stackable')]);
        }
        $manual = app(\App\Services\Marketing\CouponPricing::class)->allocate(min((int) $order->discount_cents, array_sum($netWeights)), $netWeights);
        $fidelity = app(FidelityCheckout::class)->discounts($order, $lines);
        $lineDiscounts = [];
        foreach ($lines as $line) {
            $discount = ($coupon[$line['id']] ?? 0) + ($manual[$line['id']] ?? 0) + ($fidelity[$line['id']] ?? 0);
            $discount = min($line['gross_cents'], $discount);
            $lineDiscounts[$line['id']] = $discount;
            $groups[$line['rate']] = ($groups[$line['rate']] ?? 0) + $line['gross_cents'] - $discount;
        }
        ksort($groups);
        $taxes = [];
        foreach ($groups as $rate => $amount) {
            $net = intdiv($amount * 100 + intdiv(100 + $rate, 2), 100 + $rate);
            $taxes[] = ['rate' => (int) $rate, 'gross_cents' => $amount, 'net_cents' => $net, 'vat_cents' => $amount - $net];
        }
        $gross = array_sum(array_column($lines, 'gross_cents'));

        return ['gross_cents' => $gross, 'discount_cents' => array_sum($lineDiscounts), 'coupon_discount_cents' => array_sum($coupon), 'total_cents' => $gross - array_sum($lineDiscounts), 'taxes' => $taxes, 'line_discounts' => $lineDiscounts];
    }

    public function lines(Order $order): array
    {
        $lines = [];
        foreach ($order->items()->whereNull('order_courses.rejected_at')->orderBy('order_items.id')->get() as $item) {
            $lines[] = ['id' => $item->id, 'product_id' => $item->product_id, 'name' => $item->product_name, 'quantity' => (int) $item->quantity, 'unit_price_cents' => (int) $item->unit_price_cents, 'gross_cents' => (int) ($item->quantity * $item->unit_price_cents), 'rate' => (int) $item->vat];
        }
        foreach (['covers' => ['Coperto', (int) $order->covers, (int) $order->cover_price_cents], 'delivery' => ['Consegna', 1, (int) $order->delivery_fee_cents], 'service' => ['Servizio', 1, $order->serviceFeeCents()]] as $id => [$name,$quantity,$price]) {
            if ($quantity * $price > 0) {
                $lines[] = ['id' => $id, 'product_id' => null, 'name' => $name, 'quantity' => $quantity, 'unit_price_cents' => $price, 'gross_cents' => $quantity * $price, 'rate' => (int) $order->auxiliary_vat];
            }
        }

        return $lines;
    }
}

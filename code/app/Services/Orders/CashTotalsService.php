<?php

namespace App\Services\Orders;

use App\Models\Order;

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
        foreach ($order->items()->whereNull('order_courses.rejected_at')->get() as $item) {
            $rate = (int) $item->vat;
            $groups[$rate] = ($groups[$rate] ?? 0) + $item->quantity * $item->unit_price_cents;
        }
        $aux = $order->covers * $order->cover_price_cents + (int) $order->delivery_fee_cents + $order->serviceFeeCents();
        $rate = (int) $order->auxiliary_vat;
        $groups[$rate] = ($groups[$rate] ?? 0) + $aux;
        ksort($groups);
        $gross = array_sum($groups);
        $discount = min((int) $order->discount_cents, $gross);
        $allocations = $remainders = [];
        foreach ($groups as $rate => $amount) {
            $allocations[$rate] = $gross ? intdiv($discount * $amount, $gross) : 0;
            $remainders[$rate] = $gross ? ($discount * $amount) % $gross : 0;
        }
        arsort($remainders, SORT_NUMERIC);
        $left = $discount - array_sum($allocations);
        foreach ($remainders as $rate => $remainder) {
            if ($left-- > 0) {
                $allocations[$rate]++;
            }
        }
        $taxes = [];
        foreach ($groups as $rate => $amount) {
            $amount -= $allocations[$rate];
            $net = intdiv($amount * 100 + intdiv(100 + $rate, 2), 100 + $rate);
            $taxes[] = ['rate' => $rate, 'gross_cents' => $amount, 'net_cents' => $net, 'vat_cents' => $amount - $net];
        }

        return ['gross_cents' => $gross, 'discount_cents' => $discount, 'total_cents' => $gross - $discount, 'taxes' => $taxes];
    }
}

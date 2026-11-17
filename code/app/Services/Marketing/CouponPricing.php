<?php

namespace App\Services\Marketing;

use App\Models\Order;

class CouponPricing
{
    /** Monetary effects are per line and in cents; original production prices remain intact. */
    public function discounts(Order $order, array $lines): array
    {
        if (! $order->booking_id || $order->coupon_disabled) {
            return [];
        }
        $usage = \App\Models\CouponUsage::where('booking_id', $order->booking_id)->first();
        if (! $usage) {
            return [];
        }
        // A booking can span multiple orders; its benefit belongs to one bill only.
        $bill = Order::where('booking_id', $order->booking_id)->where('status', '<>', 'cancelled')->orderBy('created_at')->orderBy('id')->value('id');
        if ($bill !== $order->id || $this->reason($order, $usage->snapshot) !== null) {
            return [];
        }
        $benefit = $usage->snapshot['benefit'];
        $eligible = [];
        foreach ($lines as $line) {
            if (in_array($line['id'], ['covers', 'service', 'delivery'], true)) {
                continue;
            }
            if (! empty($benefit['product_ids']) && ! in_array($line['product_id'], $benefit['product_ids'], true)) {
                continue;
            }
            $eligible[$line['id']] = $line;
        }
        $gross = array_sum(array_column($eligible, 'gross_cents'));
        if ($gross <= 0) {
            return [];
        }
        $type = $benefit['type'];
        $value = (int) ($benefit['value'] ?? 0);
        if ($type === 'gift') {
            $quantity = (int) ($benefit['quantity'] ?? 1);
            if (($benefit['quantity_basis'] ?? 'fixed') === 'children') {
                $quantity *= (int) ($usage->snapshot['context']['children'] ?? 0);
            }
            uasort($eligible, fn ($a, $b) => [$a['unit_price_cents'], $a['id']] <=> [$b['unit_price_cents'], $b['id']]);
            $discounts = [];
            foreach ($eligible as $id => $line) {
                $units = min($quantity, $line['quantity']);
                $discounts[$id] = $units * $line['unit_price_cents'];
                $quantity -= $units;
            }
            if (! empty($benefit['max_discount']) && array_sum($discounts) > (int) $benefit['max_discount']) {
                return $this->allocate((int) $benefit['max_discount'], $discounts);
            }

            return $discounts;
        }
        $target = match ($type) {
            'percent' => intdiv($gross * $value + 5000, 10000),
            'amount' => min($value, $gross),
            'fixed_price' => max(0, $gross - $value * (($benefit['basis'] ?? 'package') === 'person' ? $order->covers : (($benefit['basis'] ?? 'package') === 'item' ? array_sum(array_column($eligible, 'quantity')) : 1))),
            default => 0,
        };
        if (! empty($benefit['max_discount'])) {
            $target = min($target, (int) $benefit['max_discount']);
        }

        return $this->allocate(min($target, $gross), array_column($eligible, 'gross_cents', 'id'));
    }

    public function reason(Order $order, ?array $snapshot = null): ?string
    {
        $snapshot ??= \App\Models\CouponUsage::where('booking_id', $order->booking_id)->first()?->snapshot;
        if (! $snapshot) {
            return null;
        }
        $date = $order->service_date->toDateString();
        $rules = $snapshot['rules'];
        if ((! empty($snapshot['valid_from']) && $date < $snapshot['valid_from']) || (! empty($snapshot['valid_until']) && $date > $snapshot['valid_until'])) {
            return 'invalid_date';
        }
        if (! empty($rules['weekdays']) && ! in_array($order->service_date->dayOfWeekIso, array_map('intval', $rules['weekdays']), true)) {
            return 'invalid_day';
        }
        if ($order->covers < ($rules['min_pax'] ?? 1) || (! empty($rules['max_pax']) && $order->covers > $rules['max_pax']) || ($snapshot['context']['children'] ?? 0) > $order->covers) {
            return 'invalid_pax';
        }

        return null;
    }

    public function allocate(int $amount, array $weights): array
    {
        $sum = array_sum($weights);
        $result = $rest = [];
        foreach ($weights as $key => $weight) {
            $result[$key] = $sum ? intdiv($amount * $weight, $sum) : 0;
            $rest[$key] = $sum ? ($amount * $weight) % $sum : 0;
        }
        arsort($rest, SORT_NUMERIC);
        $left = $amount - array_sum($result);
        foreach ($rest as $key => $value) {
            if ($left-- <= 0) {
                break;
            } $result[$key]++;
        }

        return $result;
    }

    public function toggle(Order $order): void
    {
        app(\App\Services\Orders\OrderService::class)->authorize(\App\Services\Personnel\PersonnelPermissionsService::CASH_REGISTER);
        \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            app(CouponService::class)->check($order->status === 'open' && ! $order->cash_snapshot && ! $order->payments()->exists(), 'bill_locked');
            $order->update(['coupon_disabled' => ! $order->coupon_disabled]);
            $order->events()->create(['user_id' => auth()->id(), 'action' => 'coupon_toggled', 'details' => ['disabled' => $order->coupon_disabled]]);
        });
    }
}

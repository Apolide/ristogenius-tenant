<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class CashBillPricingService
{
    public function snapshot(Order $order): array
    {
        $totals = app(CashTotalsService::class)->snapshot($order);
        $lines = app(CashTotalsService::class)->lines($order);
        $this->check(count($lines) <= 1000 && array_sum(array_column($lines, 'quantity')) <= 10000, __('cash_desk.messages.too_many_items_for_item_based_allocation'));
        $this->check(array_sum(array_column($lines, 'gross_cents')) === (int) $totals['gross_cents'], __('cash_desk.messages.lines_do_not_match_the_saved_bill'));
        $units = [];
        $taxes = [];
        foreach ($totals['taxes'] as $tax) {
            $group = array_filter($lines, fn ($line) => $line['rate'] === (int) $tax['rate']);
            $gross = array_sum(array_column($group, 'gross_cents'));
            $discount = $gross - (int) $tax['gross_cents'];
            $this->check($discount >= 0 && $discount <= $gross, __('cash_desk.messages.vat_summary_does_not_match_products'));
            $allocated = $rest = [];
            foreach ($group as $key => $line) {
                $allocated[$key] = $gross ? intdiv($discount * $line['gross_cents'], $gross) : 0;
                $rest[$key] = $gross ? ($discount * $line['gross_cents']) % $gross : 0;
            }
            arsort($rest, SORT_NUMERIC);
            $left = $discount - array_sum($allocated);
            foreach ($rest as $key => $value) {
                if ($left-- > 0) {
                    $allocated[$key]++;
                }
            }
            foreach ($group as $key => $line) {
                $net = $line['gross_cents'] - ($totals['line_discounts'][$line['id']] ?? $allocated[$key]);
                $base = intdiv($net, $line['quantity']);
                $remainder = $net % $line['quantity'];
                for ($i = 0; $i < $line['quantity']; $i++) {
                    $units[] = ['id' => $line['id'].':'.$i, 'line_id' => $line['id'], 'name' => $line['name'],
                        'amount_cents' => $base + ($i < $remainder ? 1 : 0), 'rate' => $line['rate']];
                }
            }
            $taxes[] = ['rate' => (int) $tax['rate'], 'gross_cents' => (int) $tax['gross_cents']];
        }
        $this->check(array_sum(array_column($units, 'amount_cents')) === $order->totalCents(), __('cash_desk.messages.allocation_does_not_match_the_total'));

        // Rebuild keys explicitly: MySQL JSON reorders object keys after storage.
        return ['total_cents' => $order->totalCents(), 'gross_cents' => (int) $totals['gross_cents'], 'discount_cents' => (int) $totals['discount_cents'], 'taxes' => $taxes, 'units' => $units];
    }

    public function hash(array $pricing): string
    {
        return hash('sha256', json_encode($pricing, JSON_THROW_ON_ERROR));
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}

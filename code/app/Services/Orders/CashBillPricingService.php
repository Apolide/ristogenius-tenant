<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class CashBillPricingService
{
    public function snapshot(Order $order): array
    {
        $totals = app(CashTotalsService::class)->snapshot($order);
        $lines = [];
        foreach ($order->items()->whereNull('order_courses.rejected_at')->orderBy('order_items.id')->get() as $item) {
            $lines[] = ['id' => $item->id, 'name' => $item->product_name, 'quantity' => (int) $item->quantity,
                'gross_cents' => (int) ($item->quantity * $item->unit_price_cents), 'rate' => (int) $item->vat];
        }
        if ($order->covers > 0 && $order->cover_price_cents > 0) {
            $lines[] = ['id' => 'covers', 'name' => 'Coperto', 'quantity' => (int) $order->covers,
                'gross_cents' => (int) ($order->covers * $order->cover_price_cents), 'rate' => (int) $order->auxiliary_vat];
        }
        foreach (['delivery' => ['Consegna', (int) $order->delivery_fee_cents], 'service' => ['Servizio', $order->serviceFeeCents()]] as $key => [$name, $amount]) {
            if ($amount > 0) {
                $lines[] = ['id' => $key, 'name' => $name, 'quantity' => 1, 'gross_cents' => $amount, 'rate' => (int) $order->auxiliary_vat];
            }
        }
        $this->check(count($lines) <= 1000 && array_sum(array_column($lines, 'quantity')) <= 10000, 'Troppi prodotti per la ripartizione per articolo.');
        $this->check(array_sum(array_column($lines, 'gross_cents')) === (int) $totals['gross_cents'], 'Le righe non coincidono con il conto salvato.');
        $units = [];
        $taxes = [];
        foreach ($totals['taxes'] as $tax) {
            $group = array_filter($lines, fn ($line) => $line['rate'] === (int) $tax['rate']);
            $gross = array_sum(array_column($group, 'gross_cents'));
            $discount = $gross - (int) $tax['gross_cents'];
            $this->check($discount >= 0 && $discount <= $gross, 'Riepilogo IVA non coerente con i prodotti.');
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
                $net = $line['gross_cents'] - $allocated[$key];
                $base = intdiv($net, $line['quantity']);
                $remainder = $net % $line['quantity'];
                for ($i = 0; $i < $line['quantity']; $i++) {
                    $units[] = ['id' => $line['id'].':'.$i, 'line_id' => $line['id'], 'name' => $line['name'],
                        'amount_cents' => $base + ($i < $remainder ? 1 : 0), 'rate' => $line['rate']];
                }
            }
            $taxes[] = ['rate' => (int) $tax['rate'], 'gross_cents' => (int) $tax['gross_cents']];
        }
        $this->check(array_sum(array_column($units, 'amount_cents')) === $order->totalCents(), 'La ripartizione non quadra con il totale.');

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

<?php

namespace App\Services\PublicOrders;

use App\Models\Order;
use App\Models\OrderCourse;
use App\Services\TenantBrandingService;

class OrderSummaryService
{
    public function snapshot(Order $order, ?OrderCourse $onlyCourse = null): array
    {
        $order->loadMissing(['courses.items.modifiers', 'payments']);
        $courses = $onlyCourse ? collect([$onlyCourse->loadMissing('items.modifiers')]) : $order->courses;
        $items = [];
        foreach ($courses as $course) {
            foreach ($course->items as $item) {
                $items[] = [
                    'name' => $item->product_name, 'menu' => $item->menu_name, 'quantity' => (int) $item->quantity,
                    'unit_price_cents' => (int) $item->unit_price_cents,
                    'total_cents' => $course->rejected_at ? 0 : $item->quantity * $item->unit_price_cents,
                    'vat' => (int) $item->vat, 'notes' => $item->notes, 'course_notes' => $course->customer_notes,
                    'rejected' => (bool) $course->rejected_at,
                    'modifiers' => $item->modifiers->map(fn ($m) => $m->only(['ingredient_name', 'action', 'price_cents']))->all(),
                ];
            }
        }
        $branding = app(TenantBrandingService::class)->branding();
        $summary = [
            'restaurant' => $branding['name'], 'restaurant_address' => $branding['address'],
            'reference' => $order->reference ?: 'Tavolo '.$order->table_name,
            'fulfillment' => $order->fulfillmentLabel('it'), 'status' => $order->status === 'cancelled' ? 'Annullato' : ($order->fulfillmentStatusLabel('it') ?: 'Aperto'),
            'created_at' => $order->created_at?->format('d/m/Y H:i'), 'requested_at' => $order->requested_at?->format('d/m/Y H:i'),
            'customer_name' => $order->customer_name, 'customer_email' => $order->customer_email, 'customer_phone' => $order->customer_phone,
            'address' => $order->delivery_address, 'neighborhood' => $order->delivery_neighborhood,
            'table' => $order->table_name, 'room' => $order->room_name, 'notes' => $order->customer_notes,
            'items' => $items, 'subtotal_cents' => array_sum(array_column($items, 'total_cents')),
            'covers' => (int) $order->covers, 'cover_price_cents' => (int) $order->cover_price_cents,
            'delivery_fee_cents' => (int) $order->delivery_fee_cents, 'service_fee_cents' => $order->serviceFeeCents(),
            'discount_cents' => $order->effectiveDiscountCents(), 'discount_reason' => $order->discount_reason,
            'tip_cents' => (int) $order->payments->sum('tip_cents'),
            'total_cents' => $order->totalCents(),
            'payment_method' => ['cash' => 'Contanti', 'card' => 'Carta', 'voucher' => 'Buono', 'manual' => 'Registrazione manuale'][$order->payment_method ?? $order->payments->last()?->method ?? 'manual'] ?? $order->payment_method,
            'payment_status' => $order->status === 'paid' ? 'Pagato' : ($order->payments->sum('amount_cents') > 0 ? 'Parzialmente pagato' : 'Non pagato'),
            'paid_cents' => (int) $order->payments->sum('amount_cents'), 'paid_at' => $order->paid_at?->format('d/m/Y H:i'),
        ];
        if ($onlyCourse) {
            $customer = \App\Models\Customer::find($onlyCourse->customer_id);
            $summary['customer_name'] = $onlyCourse->customer_name;
            $summary['customer_email'] = $customer?->email;
            $summary['customer_phone'] = $customer?->phone;
            $summary['notes'] = $onlyCourse->customer_notes;
            $summary['covers'] = 0;
            $summary['cover_price_cents'] = 0;
            $summary['delivery_fee_cents'] = 0;
            $summary['service_fee_cents'] = intdiv($summary['subtotal_cents'] * $onlyCourse->service_percentage_bps + 5000, 10000);
            $summary['total_cents'] = $summary['subtotal_cents'] + $summary['service_fee_cents'];
            $summary['paid_cents'] = 0;
            $summary['discount_cents'] = 0;
            $summary['tip_cents'] = 0;
        }

        return $summary;
    }

    public function proposed(Order $order, array $quote): array
    {
        $summary = $this->snapshot($order);
        $summary['status'] = 'Proposta di modifica';
        $summary['requested_at'] = \Carbon\Carbon::parse($quote['changes']['requested_at'])->format('d/m/Y H:i');
        $summary['notes'] = $quote['changes']['customer_notes'];
        $summary['items'] = array_map(fn ($line) => [
            'name' => $line['attributes']['product_name'], 'menu' => $line['attributes']['menu_name'],
            'quantity' => $line['attributes']['quantity'], 'unit_price_cents' => $line['attributes']['unit_price_cents'],
            'total_cents' => $line['attributes']['quantity'] * $line['attributes']['unit_price_cents'],
            'vat' => $line['attributes']['vat'], 'notes' => $line['attributes']['notes'], 'course_notes' => null, 'rejected' => false,
            'modifiers' => $line['modifiers'],
        ], $quote['lines']);
        $summary['subtotal_cents'] = $quote['subtotal'];
        $summary['delivery_fee_cents'] = $quote['changes']['delivery_fee_cents'];
        $summary['neighborhood'] = $quote['changes']['delivery_neighborhood'];
        $summary['discount_cents'] = min((int) $order->discount_cents, $quote['subtotal'] + $quote['changes']['delivery_fee_cents']);
        $summary['total_cents'] = $quote['total'];

        return $summary;
    }
}

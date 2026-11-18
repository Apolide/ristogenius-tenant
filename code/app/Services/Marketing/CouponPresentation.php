<?php

namespace App\Services\Marketing;

class CouponPresentation
{
    public function summary(array $snapshot, string $language): array
    {
        $benefit = $snapshot['benefit'] ?? [];
        $type = $benefit['type'] ?? 'gift';
        $value = ($benefit['value'] ?? 0) / 100;
        $amount = number_format($value, 2, $language === 'en' ? '.' : ',', $language === 'en' ? ',' : '.').' €';
        $description = match ($type) {
            'percent' => rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').'%',
            'amount' => $amount,
            'fixed_price' => $amount.' · '.__('marketing.'.($benefit['basis'] ?? 'package'), [], $language),
            default => (string) ($benefit['quantity'] ?? 1).' · '.__('marketing.'.(($benefit['quantity_basis'] ?? 'fixed') === 'children' ? 'per_child' : 'fixed_quantity'), [], $language),
        };
        $summary = [__('marketing.offer_benefit', [], $language) => __('marketing.'.$type, [], $language).': '.$description];
        foreach (['valid_from', 'valid_until'] as $field) {
            if (!empty($snapshot[$field])) {
                $summary[__('marketing.'.$field, [], $language)] = \Carbon\Carbon::parse($snapshot[$field])->format('d/m/Y');
            }
        }
        if (!empty($snapshot['rules']['min_pax'])) {
            $summary[__('marketing.min_pax', [], $language)] = (string) $snapshot['rules']['min_pax'];
        }

        return $summary;
    }
}

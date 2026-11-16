<?php

namespace App\Services\PublicOrders;

use App\Services\Settings\TenantSettingsService;

class OrderingSettings
{
    private const DEFAULTS = [
        'enabled' => false, 'pickup_enabled' => true, 'delivery_enabled' => false, 'cash_enabled' => true,
        'delivery_minimum_cents' => 0, 'delivery_fee_cents' => 0, 'delivery_free_over_cents' => 0,
        'preparation_minutes' => 30, 'lead_minutes' => 15, 'slot_minutes' => 15, 'advance_days' => 14,
    ];

    public function get(): array
    {
        return $this->normalize(array_replace(self::DEFAULTS, app(TenantSettingsService::class)->settings()['customer_orders'] ?? []));
    }

    public function save(array $data): void
    {
        app(TenantSettingsService::class)->updateSection('customer_orders', $this->normalize($data));
    }

    private function normalize(array $data): array
    {
        // Number inputs can arrive as strings; Carbon requires numeric arguments.
        foreach (self::DEFAULTS as $key => $default) {
            if (array_key_exists($key, $data)) {
                $data[$key] = is_bool($default) ? (bool) $data[$key] : (int) $data[$key];
            }
        }

        return $data;
    }
}

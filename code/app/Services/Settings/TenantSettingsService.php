<?php

namespace App\Services\Settings;

use App\Models\TenantProfile;

class TenantSettingsService
{
    public const DAYS = [
        'lunedi' => ['label' => 'Lunedi', 'short' => 'Lun'],
        'martedi' => ['label' => 'Martedi', 'short' => 'Mar'],
        'mercoledi' => ['label' => 'Mercoledi', 'short' => 'Mer'],
        'giovedi' => ['label' => 'Giovedi', 'short' => 'Gio'],
        'venerdi' => ['label' => 'Venerdi', 'short' => 'Ven'],
        'sabato' => ['label' => 'Sabato', 'short' => 'Sab'],
        'domenica' => ['label' => 'Domenica', 'short' => 'Dom'],
    ];

    public const MEALS = [
        'pranzo' => 'Pranzo',
        'cena' => 'Cena',
    ];

    public function profile(): TenantProfile
    {
        return TenantProfile::query()->first()
            ?: TenantProfile::query()->create(['name' => config('tenant.name', config('app.name'))]);
    }

    public function settings(): array
    {
        return array_replace_recursive($this->defaults(), $this->profile()->settings ?? []);
    }

    public function updateSection(string $section, array $value): void
    {
        $profile = $this->profile();
        $settings = array_replace_recursive($this->defaults(), $profile->settings ?? []);
        $settings[$section] = $value;

        $profile->update(['settings' => $settings]);
    }

    public function defaults(): array
    {
        return [
            'reservations' => [
                'table_stay_minutes' => 90,
                'notification_channels' => ['email'],
                'opening_hours' => $this->defaultOpeningHours(),
                'pax_capacity' => [
                    'fallback' => 20,
                    'weekly' => $this->defaultWeeklyPax(),
                ],
            ],
            'automations' => [
                'reservation_reminder_hours' => 24,
            ],
        ];
    }

    public function defaultOpeningHours(): array
    {
        $hours = [
            'timerange' => 30,
            'weekly' => [],
        ];

        foreach (array_keys(self::DAYS) as $day) {
            $hours['weekly'][$day] = [
                'pranzo' => ['open' => true, 'start' => '12:00', 'end' => '15:00'],
                'cena' => ['open' => true, 'start' => '19:00', 'end' => '22:30'],
            ];
        }

        return $hours;
    }

    public function defaultWeeklyPax(): array
    {
        $weekly = [];

        foreach (array_keys(self::DAYS) as $day) {
            $weekly[$day] = [
                'pranzo' => [],
                'cena' => [],
            ];
        }

        return $weekly;
    }

    public function slots(string $start, string $end, int $minutes): array
    {
        $slots = [];
        $startAt = strtotime($start);
        $endAt = strtotime($end);

        if (! $startAt || ! $endAt || $endAt <= $startAt || ! in_array($minutes, [15, 30, 60], true)) {
            return $slots;
        }

        for ($current = $startAt; $current < $endAt; $current += $minutes * 60) {
            $next = min($current + $minutes * 60, $endAt);
            $slots[] = [
                'start' => date('H:i', $current),
                'end' => date('H:i', $next),
                'label' => date('H:i', $current).' - '.date('H:i', $next),
            ];
        }

        return $slots;
    }
}

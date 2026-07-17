<?php

namespace App\Services\Personnel;

class PersonnelService
{
    public const DEFAULT_LANGUAGE = 'it';

    public const ROLES = [
        'admin',
        'manager',
        'operator',
        'kiosk',
    ];

    /**
     * @return list<string>
     */
    public function roles(): array
    {
        return self::ROLES;
    }

    /**
     * @return array<string, string>
     */
    public function languages(): array
    {
        return collect((array) config('tenant.backend_languages', []))
            ->mapWithKeys(fn (array $meta, string $code): array => [
                $code => trim(($meta['flag'] ?? '').' '.($meta['label'] ?? strtoupper($code))),
            ])
            ->all();
    }
}

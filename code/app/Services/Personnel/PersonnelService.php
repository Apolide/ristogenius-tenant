<?php

namespace App\Services\Personnel;

use App\Services\CustomerLanguageService;

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
        return collect(app(CustomerLanguageService::class)->backend())
            ->mapWithKeys(fn (array $meta, string $code): array => [
                $code => trim(($meta['flag'] ?? '').' '.($meta['label'] ?? strtoupper($code))),
            ])
            ->all();
    }
}

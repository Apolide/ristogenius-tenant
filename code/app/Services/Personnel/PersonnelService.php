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
        $configured = explode(',', (string) config('tenant.languages', self::DEFAULT_LANGUAGE));
        $languages = array_values(array_unique(array_filter(array_map(
            fn (string $language): string => strtolower(trim($language)),
            $configured
        ))));

        if ($languages === []) {
            $languages = [self::DEFAULT_LANGUAGE];
        }

        return collect($languages)
            ->mapWithKeys(fn (string $language): array => [$language => strtoupper($language)])
            ->all();
    }
}

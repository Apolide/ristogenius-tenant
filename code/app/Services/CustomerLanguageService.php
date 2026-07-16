<?php

namespace App\Services;

class CustomerLanguageService
{
    public const LANGUAGES = [
        'it' => ['label' => 'Italiano', 'flag' => '🇮🇹'],
        'en' => ['label' => 'English', 'flag' => '🇬🇧'],
        'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪'],
        'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
        'es' => ['label' => 'Español', 'flag' => '🇪🇸'],
    ];

    public function enabled(): array
    {
        $configured = array_filter(array_map('trim', explode(',', (string) config('tenant.customer_languages'))));

        return collect($configured)->mapWithKeys(fn (string $code): array => [
            $code => self::LANGUAGES[$code] ?? ['label' => strtoupper($code), 'flag' => '🌐'],
        ])->all();
    }

    public function meta(?string $code): array
    {
        $code = strtolower($code ?: 'it');

        return self::LANGUAGES[$code] ?? ['label' => strtoupper($code), 'flag' => '🌐'];
    }
}

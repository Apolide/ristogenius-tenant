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

    public function default(): string
    {
        $default = strtolower((string) config('tenant.default_backend_language', 'it'));
        $enabled = $this->enabled();

        return isset($enabled[$default]) ? $default : (array_key_first($enabled) ?? $default);
    }

    public function meta(?string $code): array
    {
        $code = strtolower($code ?: 'it');

        return self::LANGUAGES[$code] ?? ['label' => strtoupper($code), 'flag' => '🌐'];
    }
}

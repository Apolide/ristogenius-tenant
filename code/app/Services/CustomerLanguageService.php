<?php

namespace App\Services;

class CustomerLanguageService
{
    public function enabled(): array
    {
        return $this->configured('customer_languages');
    }

    public function backend(): array
    {
        return $this->configured('backend_languages');
    }

    public function default(): string
    {
        $default = $this->backendDefault();
        $enabled = $this->enabled();

        return isset($enabled[$default]) ? $default : (array_key_first($enabled) ?? $default);
    }

    public function backendDefault(): string
    {
        $default = strtolower((string) config('tenant.default_backend_language', 'it'));
        $enabled = $this->backend();

        return isset($enabled[$default]) ? $default : (array_key_first($enabled) ?? 'it');
    }

    public function htmlLocale(?string $code = null): string
    {
        $code ??= app()->getLocale();

        return $code === 'gr' ? 'el' : str_replace('_', '-', $code);
    }

    public function meta(?string $code): array
    {
        $code = strtolower($code ?: 'it');

        return config('locales')[$code] ?? ['label' => strtoupper($code), 'flag' => '🌐'];
    }

    private function configured(string $setting): array
    {
        $catalog = config('locales');
        $languages = explode(',', strtolower((string) config('tenant.'.$setting)));
        $enabled = [];

        foreach ($languages as $language) {
            $code = trim($language);
            if (isset($catalog[$code])) {
                $enabled[$code] = $catalog[$code];
            }
        }

        return $enabled ?: ['it' => $catalog['it']];
    }
}

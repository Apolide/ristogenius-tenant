<?php

namespace App\Services;

use App\Services\Settings\TenantSettingsService;

class TenantBrandingService
{
    public function __construct(
        private TenantSettingsService $settings,
        private TenantPublicAssetService $assets,
    ) {}

    public function branding(): array
    {
        $profile = $this->settings->profile();
        $logo = $this->assets->firstUrlForBaseName('logo', 'logo');

        return [
            'name' => $profile->name ?: config('tenant.name', config('app.name')),
            'logo_url' => $logo ? $this->absoluteUrl($logo) : null,
        ];
    }

    private function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';

            return rtrim(config('app.url'), '/').'/'.ltrim($path, '/');
        }

        return rtrim(config('app.url'), '/').'/'.ltrim($url, '/');
    }
}

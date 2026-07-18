<?php

namespace Database\Seeders;

use App\Models\TenantProfile;
use App\Services\Settings\TenantSettingMessageTemplatesService;
use App\Services\Settings\TenantSettingsService;
use Illuminate\Database\Seeder;

class TenantSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $profile = TenantProfile::query()->first()
            ?: TenantProfile::query()->create(['name' => config('tenant.name', config('app.name'))]);

        $settings = array_replace_recursive(
            app(TenantSettingsService::class)->defaults(),
            $profile->settings ?? []
        );

        $messageSettings = array_replace_recursive(
            ['templates' => TenantSettingMessageTemplatesService::MESSAGE_TEMPLATE_DEFAULTS],
            $profile->message_settings ?? []
        );

        $profile->update([
            'settings' => $settings,
            'message_settings' => $messageSettings,
        ]);
    }
}

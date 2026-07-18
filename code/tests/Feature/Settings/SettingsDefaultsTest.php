<?php

namespace Tests\Feature\Settings;

use App\Models\TenantProfile;
use App\Services\Settings\TenantSettingMessageTemplatesService;
use App\Services\Settings\TenantSettingsService;
use Database\Seeders\TenantSettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsDefaultsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('tenant.name', 'Ristorante Test');
    }

    public function test_settings_routes_are_grouped_by_setting_type(): void
    {
        $this->assertSame('/manage/settings', route('settings.index', absolute: false));
        $this->assertSame('/manage/settings/opening-hours', route('settings.opening-hours', absolute: false));
        $this->assertSame('/manage/settings/pax-capacity', route('settings.pax-capacity', absolute: false));
        $this->assertSame('/manage/settings/max-sitting-time', route('settings.max-sitting-time', absolute: false));
        $this->assertSame('/manage/settings/notification-modes', route('settings.notification-modes', absolute: false));
        $this->assertSame('/manage/settings/booking-remind-hours', route('settings.booking-remind-hours', absolute: false));
        $this->assertSame('/manage/settings/message-channel-cases', route('settings.message-channel-cases', absolute: false));
        $this->assertSame('/manage/settings/messages', route('settings.messages', absolute: false));
        $this->assertSame('/manage/settings/rooms', route('settings.rooms', absolute: false));
        $this->assertSame('/manage/settings/room-tables', route('settings.room-tables', absolute: false));
    }

    public function test_tenant_settings_service_returns_default_general_settings_when_json_is_missing(): void
    {
        TenantProfile::query()->create(['name' => 'Ristorante Test']);

        $settings = app(TenantSettingsService::class)->settings();

        $this->assertSame(90, $settings['reservations']['table_stay_minutes']);
        $this->assertSame(['email'], $settings['reservations']['notification_channels']);
        $this->assertSame(30, $settings['reservations']['opening_hours']['timerange']);
        $this->assertTrue($settings['reservations']['opening_hours']['weekly']['lunedi']['pranzo']['open']);
        $this->assertSame('12:00', $settings['reservations']['opening_hours']['weekly']['lunedi']['pranzo']['start']);
        $this->assertSame(20, $settings['reservations']['pax_capacity']['fallback']);
        $this->assertSame(24, $settings['automations']['reservation_reminder_hours']);
        $this->assertSame([], $settings['messages']['message_channel_cases']);
    }

    public function test_tenant_settings_seeder_persists_defaults_in_tenant_profile_json_columns(): void
    {
        $this->seed(TenantSettingsSeeder::class);

        $profile = TenantProfile::query()->firstOrFail();

        $this->assertSame('Ristorante Test', $profile->name);
        $this->assertSame(90, $profile->settings['reservations']['table_stay_minutes']);
        $this->assertSame(['email'], $profile->settings['reservations']['notification_channels']);
        $this->assertArrayHasKey('booking_accepted', $profile->message_settings['templates']);
        $this->assertSame(
            TenantSettingMessageTemplatesService::MESSAGE_TEMPLATE_DEFAULTS['booking_accepted']['label'],
            $profile->message_settings['templates']['booking_accepted']['label']
        );
    }
}

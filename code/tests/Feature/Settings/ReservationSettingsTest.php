<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\BookingRemindHoursEdit;
use App\Livewire\Settings\MaxSittingTimeEdit;
use App\Livewire\Settings\NotificationModesEdit;
use App\Livewire\Settings\OpeningHoursEdit;
use App\Livewire\Settings\PaxCapacityEdit;
use App\Models\TenantProfile;
use App\Models\TenantSettingException;
use App\Services\Settings\TenantSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReservationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_reservation_settings_saved_in_tenant_profile_are_loaded_by_components(): void
    {
        $settings = app(TenantSettingsService::class)->defaults();
        $settings['reservations']['table_stay_minutes'] = 120;
        $settings['reservations']['notification_channels'] = ['email', 'whatsapp', 'sms'];
        $settings['reservations']['opening_hours']['timerange'] = 15;
        $settings['reservations']['opening_hours']['weekly']['lunedi']['pranzo']['open'] = false;
        $settings['reservations']['pax_capacity']['fallback'] = 34;
        $settings['reservations']['pax_capacity']['weekly']['lunedi']['cena'] = [12, 14, 16];
        $settings['automations']['reservation_reminder_hours'] = 6;

        TenantProfile::query()->create([
            'name' => 'Custom',
            'settings' => $settings,
        ]);

        Livewire::test(MaxSittingTimeEdit::class)->assertSet('table_stay_minutes', 120);
        Livewire::test(NotificationModesEdit::class)->assertSet('notification_channels', ['email', 'whatsapp', 'sms']);
        Livewire::test(BookingRemindHoursEdit::class)->assertSet('reservation_reminder_hours', 6);
        Livewire::test(OpeningHoursEdit::class)
            ->assertSet('value.timerange', 15)
            ->assertSet('value.weekly.lunedi.pranzo.open', false);
        Livewire::test(PaxCapacityEdit::class)
            ->assertSet('fallback', 34)
            ->assertSet('weekly.lunedi.cena.1', 14);
    }

    public function test_notification_modes_are_saved_as_multiple_channels_with_email_required(): void
    {
        TenantProfile::query()->create(['name' => 'Test']);

        Livewire::test(NotificationModesEdit::class)
            ->set('notification_channels', ['sms', 'telegram'])
            ->call('save')
            ->assertHasNoErrors();

        $channels = TenantProfile::query()->firstOrFail()->settings['reservations']['notification_channels'];

        $this->assertSame(['sms', 'telegram', 'email'], $channels);
    }

    public function test_max_sitting_time_and_booking_reminder_are_saved_in_their_json_sections(): void
    {
        TenantProfile::query()->create(['name' => 'Test']);

        Livewire::test(MaxSittingTimeEdit::class)
            ->set('table_stay_minutes', 150)
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(BookingRemindHoursEdit::class)
            ->set('reservation_reminder_hours', 10)
            ->call('save')
            ->assertHasNoErrors();

        $settings = TenantProfile::query()->firstOrFail()->settings;

        $this->assertSame(150, $settings['reservations']['table_stay_minutes']);
        $this->assertSame(10, $settings['automations']['reservation_reminder_hours']);
    }

    public function test_opening_hours_and_special_closing_ranges_are_saved(): void
    {
        TenantProfile::query()->create(['name' => 'Test']);
        $value = app(TenantSettingsService::class)->defaults()['reservations']['opening_hours'];
        $value['timerange'] = 60;
        $value['weekly']['venerdi']['cena'] = ['open' => true, 'start' => '20:00', 'end' => '23:00'];

        Livewire::test(OpeningHoursEdit::class)
            ->set('value', $value)
            ->call('save')
            ->assertHasNoErrors()
            ->set('new_range_start', '2026-08-10')
            ->set('new_range_end', '2026-08-15')
            ->set('new_special_pranzo', true)
            ->set('new_special_cena', false)
            ->call('addSpecialClosing')
            ->assertHasNoErrors();

        $settings = TenantProfile::query()->firstOrFail()->settings;
        $this->assertSame(60, $settings['reservations']['opening_hours']['timerange']);
        $this->assertSame('20:00', $settings['reservations']['opening_hours']['weekly']['venerdi']['cena']['start']);

        $exception = TenantSettingException::query()->firstOrFail();
        $this->assertSame(TenantSettingException::TYPE_OPENING_HOURS, $exception->type);
        $this->assertTrue($exception->payload['pranzo_closed']);
        $this->assertFalse($exception->payload['cena_closed']);
        $this->assertSame('2026-08-10', $exception->starts_on->toDateString());
        $this->assertSame('2026-08-15', $exception->ends_on->toDateString());
    }

    public function test_pax_capacity_and_range_overrides_are_saved(): void
    {
        TenantProfile::query()->create(['name' => 'Test']);

        Livewire::test(PaxCapacityEdit::class)
            ->set('fallback', 28)
            ->set('weekly.lunedi.pranzo.0', 8)
            ->set('weekly.lunedi.pranzo.1', 10)
            ->call('save')
            ->assertHasNoErrors()
            ->set('new_range_start', '2026-09-01')
            ->set('new_range_end', '2026-09-30')
            ->set('new_range_pax.pranzo', 18)
            ->set('new_range_pax.cena', 24)
            ->call('addRangeConfiguration')
            ->assertHasNoErrors();

        $settings = TenantProfile::query()->firstOrFail()->settings;
        $this->assertSame(28, $settings['reservations']['pax_capacity']['fallback']);
        $this->assertSame(10, $settings['reservations']['pax_capacity']['weekly']['lunedi']['pranzo'][1]);

        $exception = TenantSettingException::query()->firstOrFail();
        $this->assertSame(TenantSettingException::TYPE_PAX_CAPACITY, $exception->type);
        $this->assertSame(18, $exception->payload['pranzo']);
        $this->assertSame(24, $exception->payload['cena']);
    }
}

<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\MessageChannelCasesIndex;
use App\Models\TenantProfile;
use App\Services\Settings\TenantSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MessageChannelCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_message_channel_cases_are_loaded_from_settings_json(): void
    {
        TenantProfile::query()->create([
            'name' => 'Test',
            'settings' => ['messages' => ['message_channel_cases' => [
                'booking_remind' => ['channels' => ['sms', 'email']],
            ]]],
        ]);

        $service = app(TenantSettingsService::class);

        $this->assertSame(['sms', 'email'], $service->messageChannelCaseChannels('booking_remind'));
        Livewire::test(MessageChannelCasesIndex::class)
            ->assertSet('message_channel_cases.booking_remind.channels', ['sms', 'email']);
    }

    public function test_message_channel_cases_are_saved_in_settings_json(): void
    {
        TenantProfile::query()->create(['name' => 'Test']);
        $cases = app(TenantSettingsService::class)->messageChannelCases();
        $cases['booking_remind']['channels'] = ['email', 'sms'];

        Livewire::test(MessageChannelCasesIndex::class)
            ->set('message_channel_cases', $cases)
            ->call('save')
            ->assertHasNoErrors();

        $settings = TenantProfile::query()->firstOrFail()->settings;
        $this->assertSame(['email', 'sms'], $settings['messages']['message_channel_cases']['booking_remind']['channels']);
    }

    public function test_required_message_case_must_have_at_least_one_channel(): void
    {
        TenantProfile::query()->create(['name' => 'Test']);
        $cases = app(TenantSettingsService::class)->messageChannelCases();
        $cases['booking_accepted']['channels'] = [];

        Livewire::test(MessageChannelCasesIndex::class)
            ->set('message_channel_cases', $cases)
            ->call('save')
            ->assertHasErrors(['message_channel_cases.booking_accepted.channels']);
    }

    public function test_single_message_case_can_be_reset_to_its_default_channels(): void
    {
        TenantProfile::query()->create(['name' => 'Test']);

        Livewire::test(MessageChannelCasesIndex::class)
            ->set('message_channel_cases.booking_remind.channels', ['sms'])
            ->call('resetCase', 'booking_remind')
            ->assertSet(
                'message_channel_cases.booking_remind.channels',
                TenantSettingsService::MESSAGE_CHANNEL_CASES['booking_remind']['channels'],
            );
    }
}

<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\MessageChannelCasesIndex;
use App\Livewire\Settings\MessageTemplatesIndex;
use App\Models\TenantProfile;
use App\Services\Settings\TenantSettingMessageTemplatesService;
use App\Services\Settings\TenantSettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MessageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_message_channel_cases_are_loaded_from_settings_json(): void
    {
        TenantProfile::query()->create([
            'name' => 'Test',
            'settings' => [
                'messages' => [
                    'message_channel_cases' => [
                        'booking_remind' => ['channels' => ['sms', 'email']],
                    ],
                ],
            ],
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

    public function test_custom_message_templates_are_loaded_from_message_settings_json(): void
    {
        TenantProfile::query()->create([
            'name' => 'Test',
            'message_settings' => [
                'templates' => [
                    'booking_remind' => [
                        'translations' => [
                            'it' => [
                                'subject' => 'Promemoria personalizzato',
                                'message' => 'Messaggio personalizzato',
                                'additional_note' => 'Nota',
                                'sms' => 'SMS personalizzato',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $template = app(TenantSettingMessageTemplatesService::class)->messageTemplate('booking_remind');

        $this->assertSame('Promemoria personalizzato', $template['translations']['it']['subject']);
        $this->assertSame('Messaggio personalizzato', $template['translations']['it']['message']);
    }

    public function test_message_template_updates_are_saved_to_message_settings_and_removed_from_legacy_settings(): void
    {
        TenantProfile::query()->create([
            'name' => 'Test',
            'settings' => [
                'messages' => [
                    'templates' => [
                        'booking_accepted' => [
                            'translations' => [
                                'it' => ['subject' => 'Legacy subject'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Livewire::test(MessageTemplatesIndex::class)
            ->call('edit', 'booking_accepted')
            ->set('form.translations.it.subject', 'Nuovo oggetto')
            ->set('form.translations.it.message', 'Nuovo messaggio')
            ->set('form.translations.it.additional_note', 'Nuova nota')
            ->set('form.translations.it.sms', 'Nuovo SMS')
            ->set('form.translations.en.subject', 'New subject')
            ->set('form.translations.en.message', 'New message')
            ->set('form.translations.en.additional_note', 'New note')
            ->set('form.translations.en.sms', 'New SMS')
            ->call('save')
            ->assertHasNoErrors();

        $profile = TenantProfile::query()->firstOrFail();

        $this->assertSame('Nuovo oggetto', $profile->message_settings['templates']['booking_accepted']['translations']['it']['subject']);
        $this->assertArrayNotHasKey('templates', $profile->settings['messages']);
    }
}

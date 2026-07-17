<?php

namespace App\Livewire\Settings;

use App\Services\Settings\TenantSettingsService;
use Livewire\Component;

class MessageChannelCasesIndex extends Component
{
    public string $search = '';

    public array $message_channel_cases = [];

    public function mount(TenantSettingsService $settingsService): void
    {
        $this->message_channel_cases = $settingsService->messageChannelCases();
    }

    public function save(TenantSettingsService $settingsService): void
    {
        $validated = $this->validate([
            'message_channel_cases' => ['array'],
            'message_channel_cases.*.channels' => ['array'],
            'message_channel_cases.*.channels.*' => ['in:whatsapp,telegram,email,sms'],
        ]);

        foreach (TenantSettingsService::MESSAGE_CHANNEL_CASES as $key => $case) {
            $channels = $validated['message_channel_cases'][$key]['channels'] ?? [];

            if ($case['required'] && count($channels) === 0) {
                $this->addError(
                    "message_channel_cases.{$key}.channels",
                    __('message_channel_cases.required_error')
                );

                return;
            }
        }

        $settingsService->updateMessageChannelCases($validated['message_channel_cases'] ?? []);
        $this->message_channel_cases = $settingsService->messageChannelCases();

        session()->flash('success', __('message_channel_cases.updated'));
    }

    public function resetToDefaults(TenantSettingsService $settingsService): void
    {
        $this->message_channel_cases = TenantSettingsService::MESSAGE_CHANNEL_CASES;
        $settingsService->updateMessageChannelCases($this->message_channel_cases);

        session()->flash('success', __('message_channel_cases.defaults_restored'));
    }

    public function resetCase(string $key): void
    {
        if (! isset(TenantSettingsService::MESSAGE_CHANNEL_CASES[$key])) {
            return;
        }

        $this->message_channel_cases[$key]['channels'] = TenantSettingsService::MESSAGE_CHANNEL_CASES[$key]['channels'];
    }

    public function render(TenantSettingsService $settingsService)
    {
        return view('livewire.settings.message-channel-cases-index', [
            'channels' => TenantSettingsService::MESSAGE_CHANNELS,
            'cases' => $settingsService->messageChannelCases($this->search),
        ])->title(__('message_channel_cases.title'));
    }
}

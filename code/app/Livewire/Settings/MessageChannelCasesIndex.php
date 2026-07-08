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
                    'Per i messaggi obbligatori deve essere selezionato almeno un canale.'
                );

                return;
            }
        }

        $settingsService->updateMessageChannelCases($validated['message_channel_cases'] ?? []);
        $this->message_channel_cases = $settingsService->messageChannelCases();

        session()->flash('success', 'Casi invio messaggi aggiornati correttamente.');
    }

    public function resetToDefaults(TenantSettingsService $settingsService): void
    {
        $this->message_channel_cases = TenantSettingsService::MESSAGE_CHANNEL_CASES;
        $settingsService->updateMessageChannelCases($this->message_channel_cases);

        session()->flash('success', 'Casi invio messaggi ripristinati correttamente.');
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
        ])->title('Casi invio messaggi');
    }
}

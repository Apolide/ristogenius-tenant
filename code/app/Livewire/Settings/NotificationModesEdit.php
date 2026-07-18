<?php

namespace App\Livewire\Settings;

use App\Services\Settings\TenantSettingsService;
use Livewire\Component;

class NotificationModesEdit extends Component
{
    public array $notification_channels = ['email'];

    public function mount(TenantSettingsService $settingsService): void
    {
        $reservations = $settingsService->settings()['reservations'];
        $channels = $reservations['notification_channels']
            ?? $reservations['notification_channel']
            ?? ['email'];

        if (is_string($channels)) {
            $channels = $channels === 'none' ? [] : [$channels];
        }

        $this->notification_channels = $this->withRequiredEmail($channels);
    }

    public function save(TenantSettingsService $settingsService): void
    {
        $validated = $this->validate([
            'notification_channels' => ['array'],
            'notification_channels.*' => ['in:email,sms,whatsapp,telegram'],
        ]);

        $settings = $settingsService->settings();
        $reservations = $settings['reservations'];
        unset($reservations['notification_channel']);
        $reservations['notification_channels'] = $this->withRequiredEmail($validated['notification_channels'] ?? []);

        $settingsService->updateSection('reservations', $reservations);

        session()->flash('success', __('reservation_settings.notifications.updated'));
    }

    public function render()
    {
        return view('livewire.settings.notification-modes-edit')
            ->title(__('reservation_settings.notifications.title'));
    }

    private function withRequiredEmail(array $channels): array
    {
        $channels[] = 'email';

        return array_values(array_unique(array_intersect($channels, ['email', 'sms', 'whatsapp', 'telegram'])));
    }
}

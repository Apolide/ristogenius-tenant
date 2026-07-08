<?php

namespace App\Livewire\Settings;

use App\Services\Settings\TenantSettingsService;
use Livewire\Component;

class BookingRemindHoursEdit extends Component
{
    public int $reservation_reminder_hours = 24;

    public function mount(TenantSettingsService $settingsService): void
    {
        $this->reservation_reminder_hours = (int) $settingsService->settings()['automations']['reservation_reminder_hours'];
    }

    public function save(TenantSettingsService $settingsService): void
    {
        $validated = $this->validate([
            'reservation_reminder_hours' => ['required', 'integer', 'min:0', 'max:168'],
        ]);

        $settings = $settingsService->settings();
        $automations = $settings['automations'];
        $automations['reservation_reminder_hours'] = $validated['reservation_reminder_hours'];

        $settingsService->updateSection('automations', $automations);

        session()->flash('success', 'Ore promemoria prenotazione aggiornate correttamente.');
    }

    public function render()
    {
        return view('livewire.settings.booking-remind-hours-edit')
            ->title('Ore promemoria prenotazione');
    }
}

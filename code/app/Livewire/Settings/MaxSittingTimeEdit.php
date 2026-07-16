<?php

namespace App\Livewire\Settings;

use App\Services\Settings\TenantSettingsService;
use Livewire\Component;

class MaxSittingTimeEdit extends Component
{
    public int $table_stay_minutes = 90;

    public function mount(TenantSettingsService $settingsService): void
    {
        $this->table_stay_minutes = (int) $settingsService->settings()['reservations']['table_stay_minutes'];
    }

    public function save(TenantSettingsService $settingsService): void
    {
        $validated = $this->validate([
            'table_stay_minutes' => ['required', 'integer', 'min:15', 'max:480'],
        ]);

        $settings = $settingsService->settings();
        $reservations = $settings['reservations'];
        $reservations['table_stay_minutes'] = $validated['table_stay_minutes'];

        $settingsService->updateSection('reservations', $reservations);

        session()->flash('success', 'Tempo massimo permanenza aggiornato correttamente.');
    }

    public function render()
    {
        return view('livewire.settings.max-sitting-time-edit')
            ->title('Tempo massimo permanenza al tavolo');
    }
}

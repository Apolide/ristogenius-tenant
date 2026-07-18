<?php

namespace App\Livewire\Settings;

use App\Models\TenantSettingException;
use App\Services\Settings\TenantSettingsService;
use Livewire\Component;

class OpeningHoursEdit extends Component
{
    public array $value = [];
    public ?string $new_range_start = null;
    public ?string $new_range_end = null;
    public bool $new_special_pranzo = false;
    public bool $new_special_cena = false;

    public function mount(TenantSettingsService $settingsService): void
    {
        $this->value = $settingsService->settings()['reservations']['opening_hours'];
    }

    public function addSpecialClosing(): void
    {
        $validated = $this->validate([
            'new_range_start' => ['required', 'date'],
            'new_range_end' => ['required', 'date', 'after_or_equal:new_range_start'],
            'new_special_pranzo' => ['boolean'],
            'new_special_cena' => ['boolean'],
        ]);

        if (! $validated['new_special_pranzo'] && ! $validated['new_special_cena']) {
            $this->addError('new_special_pranzo', __('reservation_settings.opening.service_error'));
            return;
        }

        TenantSettingException::query()->create([
            'type' => TenantSettingException::TYPE_OPENING_HOURS,
            'starts_on' => $validated['new_range_start'],
            'ends_on' => $validated['new_range_end'],
            'payload' => [
                'pranzo_closed' => $validated['new_special_pranzo'],
                'cena_closed' => $validated['new_special_cena'],
            ],
        ]);

        $this->reset('new_range_start', 'new_range_end', 'new_special_pranzo', 'new_special_cena');
    }

    public function removeSpecialClosing(int $id): void
    {
        TenantSettingException::query()
            ->whereKey($id)
            ->where('type', TenantSettingException::TYPE_OPENING_HOURS)
            ->delete();
    }

    public function save(TenantSettingsService $settingsService): void
    {
        $validated = $this->validate([
            'value.timerange' => ['required', 'integer', 'in:15,30,60'],
            'value.weekly.*.*.open' => ['required', 'boolean'],
            'value.weekly.*.*.start' => ['required', 'date_format:H:i'],
            'value.weekly.*.*.end' => ['required', 'date_format:H:i'],
        ]);

        $settings = $settingsService->settings();
        $reservations = $settings['reservations'];
        $reservations['opening_hours'] = $validated['value'];

        $settingsService->updateSection('reservations', $reservations);

        session()->flash('success', __('reservation_settings.opening.updated'));
    }

    public function render(TenantSettingsService $settingsService)
    {
        return view('livewire.settings.opening-hours-edit', [
            'days' => $settingsService->localizedDays(),
            'meals' => $settingsService->localizedMeals(),
            'exceptions' => TenantSettingException::query()
                ->where('type', TenantSettingException::TYPE_OPENING_HOURS)
                ->orderBy('starts_on')
                ->get(),
        ])->title(__('reservation_settings.opening.title'));
    }
}

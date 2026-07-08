<?php

namespace App\Livewire\Settings;

use App\Models\TenantSettingException;
use App\Services\Settings\TenantSettingsService;
use Livewire\Component;

class PaxCapacityEdit extends Component
{
    public int $fallback = 20;
    public array $weekly = [];
    public ?string $new_range_start = null;
    public ?string $new_range_end = null;
    public array $new_range_pax = [
        'pranzo' => null,
        'cena' => null,
    ];

    public function mount(TenantSettingsService $settingsService): void
    {
        $settings = $settingsService->settings();
        $capacity = $settings['reservations']['pax_capacity'];

        $this->fallback = (int) $capacity['fallback'];
        $this->weekly = $capacity['weekly'];
    }

    public function applyFallbackToWeekly(TenantSettingsService $settingsService): void
    {
        $openingHours = $settingsService->settings()['reservations']['opening_hours'];

        foreach (TenantSettingsService::DAYS as $dayKey => $day) {
            foreach (TenantSettingsService::MEALS as $mealKey => $meal) {
                $slots = $this->slotsFor($settingsService, $openingHours, $dayKey, $mealKey);

                foreach ($slots as $index => $slot) {
                    $this->weekly[$dayKey][$mealKey][$index] = $this->fallback;
                }
            }
        }
    }

    public function addRangeConfiguration(): void
    {
        $validated = $this->validate([
            'new_range_start' => ['required', 'date'],
            'new_range_end' => ['required', 'date', 'after_or_equal:new_range_start'],
            'new_range_pax.pranzo' => ['nullable', 'integer', 'min:0'],
            'new_range_pax.cena' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validated['new_range_pax']['pranzo'] === null && $validated['new_range_pax']['cena'] === null) {
            $this->addError('new_range_pax.pranzo', 'Inserisci almeno un valore pax.');
            return;
        }

        TenantSettingException::query()->create([
            'type' => TenantSettingException::TYPE_PAX_CAPACITY,
            'starts_on' => $validated['new_range_start'],
            'ends_on' => $validated['new_range_end'],
            'payload' => [
                'pranzo' => $validated['new_range_pax']['pranzo'],
                'cena' => $validated['new_range_pax']['cena'],
            ],
        ]);

        $this->reset('new_range_start', 'new_range_end');
        $this->new_range_pax = ['pranzo' => null, 'cena' => null];
    }

    public function removeRangeConfiguration(int $id): void
    {
        TenantSettingException::query()
            ->whereKey($id)
            ->where('type', TenantSettingException::TYPE_PAX_CAPACITY)
            ->delete();
    }

    public function save(TenantSettingsService $settingsService): void
    {
        $validated = $this->validate([
            'fallback' => ['required', 'integer', 'min:0'],
            'weekly.*.*.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $settings = $settingsService->settings();
        $reservations = $settings['reservations'];
        $reservations['pax_capacity'] = [
            'fallback' => $validated['fallback'],
            'weekly' => $this->weekly,
        ];

        $settingsService->updateSection('reservations', $reservations);

        session()->flash('success', 'Configurazione pax aggiornata correttamente.');
    }

    public function render()
    {
        $settingsService = app(TenantSettingsService::class);
        $openingHours = $settingsService->settings()['reservations']['opening_hours'];
        $slots = [];

        foreach (TenantSettingsService::DAYS as $dayKey => $day) {
            foreach (TenantSettingsService::MEALS as $mealKey => $meal) {
                $slots[$dayKey][$mealKey] = $this->slotsFor($settingsService, $openingHours, $dayKey, $mealKey);
            }
        }

        return view('livewire.settings.pax-capacity-edit', [
            'days' => TenantSettingsService::DAYS,
            'meals' => TenantSettingsService::MEALS,
            'slots' => $slots,
            'exceptions' => TenantSettingException::query()
                ->where('type', TenantSettingException::TYPE_PAX_CAPACITY)
                ->orderBy('starts_on')
                ->get(),
        ])->title('Pax per slot orario');
    }

    private function slotsFor(TenantSettingsService $settingsService, array $openingHours, string $dayKey, string $mealKey): array
    {
        $meal = $openingHours['weekly'][$dayKey][$mealKey] ?? null;

        if (! $meal || ! filter_var($meal['open'], FILTER_VALIDATE_BOOLEAN)) {
            return [];
        }

        return $settingsService->slots($meal['start'], $meal['end'], (int) $openingHours['timerange']);
    }
}

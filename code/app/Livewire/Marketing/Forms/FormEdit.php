<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\FormBlueprintService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormEdit extends Component
{
    public MarketingForm $form;

    public array $translations = [];

    public array $newField = ['key' => '', 'type' => 'text', 'label' => [], 'options' => [], 'required' => false, 'visible' => true];

    public ?int $editingFieldId = null;

    public array $editField = [];

    public array $eventSchedule = [];

    public function mount(): void
    {
        $this->translations = $this->form->translations;
        foreach ($this->form->enabled_languages as $language) {
            $this->translations[$language]['booking_policy'] ??= '';
        }
        $storedSchedule = $this->form->schedule ?? [];
        $this->eventSchedule = array_replace([
            'mode' => 'single',
            'slot_mode' => array_key_exists('slots', $storedSchedule) ? 'custom' : 'standard',
            'single_date' => '',
            'dates' => [''],
            'range_start' => '',
            'range_end' => '',
            'min_guests' => 1,
            'max_guests' => 1,
            'slots' => [['time' => '', 'capacity' => 1]],
        ], $storedSchedule);
    }

    public function addEventDate(): void
    {
        $this->eventSchedule['dates'][] = '';
    }

    public function removeEventDate(int $index): void
    {
        unset($this->eventSchedule['dates'][$index]);
        $this->eventSchedule['dates'] = array_values($this->eventSchedule['dates']);
    }

    public function addEventSlot(): void
    {
        $this->eventSchedule['slots'][] = ['time' => '', 'capacity' => max(1, (int) ($this->eventSchedule['max_guests'] ?? 1))];
    }

    public function removeEventSlot(int $index): void
    {
        unset($this->eventSchedule['slots'][$index]);
        $this->eventSchedule['slots'] = array_values($this->eventSchedule['slots']);
    }

    public function saveEventSchedule(): void
    {
        abort_unless($this->form->type === 'event', 404);

        $rules = ['eventSchedule.mode' => ['required', Rule::in(['standard', 'single', 'dates', 'range'])]];

        if (($this->eventSchedule['mode'] ?? null) !== 'standard') {
            $rules['eventSchedule.slot_mode'] = ['required', Rule::in(['standard', 'custom'])];
            if (($this->eventSchedule['slot_mode'] ?? null) === 'custom') {
                $rules += [
                    'eventSchedule.min_guests' => ['required', 'integer', 'min:1'],
                    'eventSchedule.max_guests' => ['required', 'integer', 'gte:eventSchedule.min_guests'],
                    'eventSchedule.slots' => ['required', 'array', 'min:1'],
                    'eventSchedule.slots.*.time' => ['required', 'date_format:H:i', 'distinct'],
                    'eventSchedule.slots.*.capacity' => ['required', 'integer', 'min:1'],
                ];
            }
        }

        match ($this->eventSchedule['mode'] ?? null) {
            'single' => $rules['eventSchedule.single_date'] = ['required', 'date', 'after_or_equal:today'],
            'dates' => $rules['eventSchedule.dates.*'] = ['required', 'date', 'after_or_equal:today', 'distinct'],
            'range' => $rules += [
                'eventSchedule.range_start' => ['required', 'date', 'after_or_equal:today'],
                'eventSchedule.range_end' => ['required', 'date', 'after_or_equal:eventSchedule.range_start'],
            ],
            'standard' => null,
            default => null,
        };

        if (($this->eventSchedule['mode'] ?? null) === 'dates') {
            $rules['eventSchedule.dates'] = ['required', 'array', 'min:1'];
        }

        $this->validate($rules);
        $schedule = $this->eventSchedule;
        $schedule['dates'] = collect($schedule['dates'] ?? [])->filter()->unique()->sort()->values()->all();
        $schedule['slots'] = collect($schedule['slots'] ?? [])->sortBy('time')->values()->all();
        $schedule['min_guests'] = (int) $schedule['min_guests'];
        $schedule['max_guests'] = (int) $schedule['max_guests'];
        $schedule['slots'] = collect($schedule['slots'])->map(fn (array $slot): array => [
            'time' => $slot['time'],
            'capacity' => (int) $slot['capacity'],
        ])->all();

        $this->form->update(['schedule' => $schedule]);
        $this->form->refresh();
        $this->eventSchedule = $schedule;
        session()->flash('success', 'Disponibilità dell’evento aggiornata.');
    }

    public function saveDetails(): void
    {
        $rules = ['translations' => ['required', 'array']];
        foreach ($this->form->enabled_languages as $language) {
            $rules["translations.{$language}.title"] = ['required', 'string', 'max:255'];
            $rules["translations.{$language}.description"] = ['nullable', 'string'];
            if (in_array($this->form->type, ['booking', 'event'], true)) {
                $rules["translations.{$language}.booking_policy"] = ['nullable', 'string'];
            }
        }

        $this->validate($rules);
        $this->form->update(['translations' => $this->translations]);
        $this->form->refresh();
        session()->flash('success', 'Dati del form aggiornati.');
    }

    public function addField(CustomerLanguageService $languages): void
    {
        $hasOptions = in_array($this->newField['type'] ?? '', ['checkbox', 'radio', 'select'], true);
        $rules = ['newField.key' => ['required', 'alpha_dash', 'max:80', Rule::unique('marketing_form_fields', 'key')->where('marketing_form_id', $this->form->id)], 'newField.type' => ['required', Rule::in(config('marketing_forms.field_types'))], 'newField.required' => ['boolean'], 'newField.visible' => ['boolean'], 'newField.options' => ['array']];
        foreach ($this->form->enabled_languages as $lang) {
            $rules['newField.label.'.$lang] = ['required', 'string', 'max:255'];
            $rules['newField.options.'.$lang] = [$hasOptions ? 'required' : 'nullable', 'string'];
        }
        $data = $this->validate($rules)['newField'];
        $data['options'] = $hasOptions ? $this->localizedOptions($data['options']) : null;
        $data['position'] = ($this->form->fields()->max('position') ?? -1) + 1;
        $data['locked'] = false;
        $this->form->fields()->create($data);
        $this->newField = ['key' => '', 'type' => 'text', 'label' => [], 'options' => [], 'required' => false, 'visible' => true];
    }

    public function toggle(int $id, string $property, FormBlueprintService $service): void
    {
        abort_unless(in_array($property, ['required', 'visible'], true), 400);
        $field = $this->form->fields()->findOrFail($id);
        try {
            $service->updateField($this->form, $id, [$property => ! $field->{$property}]);
        } catch (\DomainException $e) {
            $this->addError('field', $e->getMessage());
        }
    }

    public function startEditingField(int $id): void
    {
        $field = $this->form->fields()->where('locked', false)->findOrFail($id);
        $this->editingFieldId = $field->id;
        $this->editField = [
            'key' => $field->key,
            'type' => $field->type,
            'label' => $field->label,
            'options' => collect($this->form->enabled_languages)->mapWithKeys(fn (string $language): array => [
                $language => implode(', ', $field->options[$language] ?? []),
            ])->all(),
            'required' => $field->required,
            'visible' => $field->visible,
        ];
        $this->resetErrorBag();
    }

    public function cancelEditingField(): void
    {
        $this->editingFieldId = null;
        $this->editField = [];
        $this->resetErrorBag();
    }

    public function updateField(FormBlueprintService $service): void
    {
        if (! $this->editingFieldId) {
            return;
        }

        $hasOptions = in_array($this->editField['type'] ?? '', ['checkbox', 'radio', 'select'], true);
        $rules = [
            'editField.type' => ['required', Rule::in(config('marketing_forms.field_types'))],
            'editField.required' => ['boolean'],
            'editField.visible' => ['boolean'],
            'editField.options' => ['array'],
        ];
        foreach ($this->form->enabled_languages as $language) {
            $rules["editField.label.{$language}"] = ['required', 'string', 'max:255'];
            $rules["editField.options.{$language}"] = [$hasOptions ? 'required' : 'nullable', 'string'];
        }
        $this->validate($rules);

        $options = $hasOptions ? $this->localizedOptions($this->editField['options']) : null;

        $service->updateField($this->form, $this->editingFieldId, [
            'type' => $this->editField['type'],
            'label' => $this->editField['label'],
            'options' => $options,
            'required' => $this->editField['required'],
            'visible' => $this->editField['visible'],
        ]);

        $this->cancelEditingField();
        session()->flash('success', 'Campo personalizzato aggiornato.');
    }

    private function localizedOptions(array $options): array
    {
        return collect($this->form->enabled_languages)->mapWithKeys(fn (string $language): array => [
            $language => collect(explode(',', $options[$language] ?? ''))
                ->map(fn (string $option): string => trim($option))->filter()->values()->all(),
        ])->all();
    }

    public function deleteField(int $id): void
    {
        $field = $this->form->fields()->findOrFail($id);
        if ($field->required) {
            $this->addError('field', 'Un campo obbligatorio non può essere eliminato. Rendilo prima facoltativo.');

            return;
        }
        $field->delete();
    }

    public function render(FormBlueprintService $blueprints, CustomerLanguageService $languages)
    {
        $fields = $this->form->fields()->get();
        $baseKeys = collect($blueprints->fields($this->form->type))->pluck('key');

        return view('livewire.marketing.forms.edit', [
            'baseFields' => $fields->whereIn('key', $baseKeys),
            'customFields' => $fields->whereNotIn('key', $baseKeys),
            'languageMeta' => $languages->enabled(),
        ])->title(__('marketing_forms.edit'));
    }
}

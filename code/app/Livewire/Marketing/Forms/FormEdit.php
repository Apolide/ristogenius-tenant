<?php

namespace App\Livewire\Marketing\Forms;

use App\Models\MarketingForm;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\FormBlueprintService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class FormEdit extends Component
{
    use WithFileUploads;

    public MarketingForm $form;

    public array $translations = [];

    public $image;

    public array $newField = ['key' => '', 'type' => 'text', 'label' => [], 'options' => [], 'required' => false, 'visible' => true];

    public ?int $editingFieldId = null;

    public array $editField = [];

    public function mount(): void
    {
        $this->translations = $this->form->translations;
    }

    public function saveDetails(): void
    {
        $rules = ['translations' => ['required', 'array'], 'image' => ['nullable', 'image', 'max:5120']];
        foreach ($this->form->enabled_languages as $language) {
            $rules["translations.{$language}.title"] = ['required', 'string', 'max:255'];
            $rules["translations.{$language}.description"] = ['nullable', 'string'];
        }

        $this->validate($rules);
        $values = ['translations' => $this->translations];
        if ($this->image) {
            $values['image_path'] = $this->image->store('marketing-forms/images', 'public');
        }
        $this->form->update($values);
        $this->form->refresh();
        $this->image = null;
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

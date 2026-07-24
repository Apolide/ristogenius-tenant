<?php

namespace App\Services\MarketingForms;

use App\Models\MarketingForm;
use Illuminate\Support\Facades\DB;

class FormBlueprintService
{
    public function types(): array
    {
        return config('marketing_forms.blueprints');
    }

    public function fields(string $type): array
    {
        $blueprint = $this->types()[$type] ?? throw new \InvalidArgumentException('Unknown form type.');

        return isset($blueprint['fields_from']) ? $this->fields($blueprint['fields_from']) : $blueprint['fields'];
    }

    public function create(array $data): MarketingForm
    {
        return DB::transaction(function () use ($data): MarketingForm {
            $form = MarketingForm::create($data);
            foreach ($this->fields($form->type) as $position => $field) {
                $form->fields()->create($field + ['position' => $position]);
            }
            if ($form->type === 'generic') {
                $form->notificationUsers()->sync($data['notify_user_ids'] ?? []);
            }

            return $form->load('fields');
        });
    }

    public function updateField(MarketingForm $form, int $id, array $values): void
    {
        $field = $form->fields()->findOrFail($id);
        if ($field->locked && (! ($values['visible'] ?? false) || ! ($values['required'] ?? false))) {
            throw new \DomainException('A mandatory standard field cannot be hidden or made optional.');
        }
        $field->update($values);
    }
}

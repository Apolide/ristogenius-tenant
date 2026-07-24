<?php

namespace Database\Seeders\Concerns;

use App\Models\MarketingForm;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\FormBlueprintService;

trait SeedsDefaultMarketingForm
{
    private function seedDefaultForm(string $slug, array $content, array $fields): bool
    {
        if (MarketingForm::query()->where('slug', $slug)->exists()) {
            return false;
        }

        $languages = array_keys(app(CustomerLanguageService::class)->enabled()) ?: ['it'];
        $translations = collect($languages)->mapWithKeys(fn (string $language): array => [
            $language => $content[$language] ?? $content['en'],
        ])->all();

        $form = app(FormBlueprintService::class)->create([
            'type' => 'generic',
            'slug' => $slug,
            'translations' => $translations,
            'enabled_languages' => $languages,
            'schedule' => null,
            'is_active' => true,
            'accepts_coupons' => false,
        ]);

        foreach ($fields as $field) {
            $field['label'] = $this->enabledTranslations($field['label'], $languages);
            if (isset($field['options'])) {
                $field['options'] = $this->enabledTranslations($field['options'], $languages);
            }
            $field['position'] = ($form->fields()->max('position') ?? -1) + 1;
            $field['locked'] = false;
            $field['visible'] = true;
            $form->fields()->create($field);
        }

        return true;
    }

    private function enabledTranslations(array $translations, array $languages): array
    {
        return collect($languages)->mapWithKeys(fn (string $language): array => [
            $language => $translations[$language] ?? $translations['en'],
        ])->all();
    }
}

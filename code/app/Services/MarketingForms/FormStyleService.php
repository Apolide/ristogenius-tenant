<?php

namespace App\Services\MarketingForms;

use App\Models\MarketingForm;

class FormStyleService
{
    public function defaults(): array
    {
        return [
            ...config('marketing_form_style.colors', []),
            ...config('marketing_form_style.layout', []),
            'background_overlay' => config('marketing_form_style.background.overlay', 35),
            'background_position' => config('marketing_form_style.background.position', 'center center'),
        ];
    }

    public function for(MarketingForm $form): array
    {
        return array_replace($this->defaults(), $form->style_settings ?? []);
    }

    public function cssVariables(array $style): string
    {
        return collect($style)->mapWithKeys(fn (mixed $value, string $key): array => [
            '--tenant-form-'.str_replace('_', '-', $key) => (string) $value,
        ])->map(fn (string $value, string $key): string => $key.': '.$value)->implode('; ');
    }
}

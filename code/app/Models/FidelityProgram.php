<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FidelityProgram extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['active' => 'boolean', 'rate' => 'integer', 'starting_points' => 'integer', 'channels' => 'array', 'copy' => 'array'];

    public function bookingUrl(string $language): ?string
    {
        if ($this->booking_url) {
            return $this->booking_url;
        }

        $form = MarketingForm::where('slug', MarketingForm::PRIMARY_BOOKING_SLUG)
            ->where('type', 'booking')->where('is_active', true)->first();
        if (! $form || ! $form->enabled_languages) {
            return null;
        }

        $language = in_array($language, $form->enabled_languages, true) ? $language : $form->enabled_languages[0];

        return route('marketing.forms.public', ['language' => $language, 'form' => $form->slug]);
    }

    public function copyFor(string $field, string $language): ?string
    {
        $localized = $this->copy[$language] ?? [];

        return array_key_exists($field, $localized) ? $localized[$field] : ($this->copy[$field] ?? null);
    }
}

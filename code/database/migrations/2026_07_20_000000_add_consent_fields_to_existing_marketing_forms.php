<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $labels = [
            'privacy_consent' => [
                'it' => 'Ho letto e accetto il documento: informativa sul trattamento dei dati personali',
                'en' => 'I have read and accept the personal data processing policy',
                'de' => 'Ich habe die Datenschutzinformation gelesen und akzeptiere sie',
            ],
            'marketing_consent' => [
                'it' => 'Acconsento a ricevere comunicazioni di marketing, offerte speciali e promozioni in conformità con l’informativa sulla privacy.',
                'en' => 'I consent to receive marketing communications, special offers and promotions in accordance with the privacy policy.',
                'de' => 'Ich stimme dem Erhalt von Marketingmitteilungen, Sonderangeboten und Werbeaktionen gemäß der Datenschutzrichtlinie zu.',
            ],
        ];

        $formIds = DB::table('marketing_form_fields')
            ->whereIn('key', ['name', 'email', 'phone'])
            ->distinct()
            ->pluck('marketing_form_id');

        foreach ($formIds as $formId) {
            $position = (int) DB::table('marketing_form_fields')->where('marketing_form_id', $formId)->max('position');

            foreach ($labels as $key => $label) {
                $field = DB::table('marketing_form_fields')->where('marketing_form_id', $formId)->where('key', $key);
                if ($field->exists()) {
                    if ($key === 'privacy_consent') {
                        $field->update([
                            'label' => json_encode($label, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'required' => true,
                            'visible' => true,
                            'locked' => true,
                            'updated_at' => now(),
                        ]);
                    }

                    continue;
                }

                DB::table('marketing_form_fields')->insert([
                    'marketing_form_id' => $formId,
                    'key' => $key,
                    'type' => 'checkbox',
                    'label' => json_encode($label, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'options' => null,
                    'required' => $key === 'privacy_consent',
                    'visible' => true,
                    'locked' => $key === 'privacy_consent',
                    'position' => ++$position,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Consent records may already contain submitted legal choices, so a
        // rollback deliberately preserves both their definition and data.
    }
};

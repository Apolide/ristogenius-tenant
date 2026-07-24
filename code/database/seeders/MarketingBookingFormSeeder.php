<?php

namespace Database\Seeders;

use App\Models\MarketingForm;
use App\Services\CustomerLanguageService;
use App\Services\MarketingForms\FormBlueprintService;
use Illuminate\Database\Seeder;

class MarketingBookingFormSeeder extends Seeder
{
    /**
     * Install the default public booking form without overwriting a form that
     * the tenant may already have customised.
     */
    public function run(): void
    {
        if (MarketingForm::query()->where('slug', 'booking')->exists()) {
            $this->command?->info('Default booking form already exists; no changes were made.');

            return;
        }

        $languages = array_keys(app(CustomerLanguageService::class)->enabled());
        $languages = $languages ?: ['it'];

        $content = [
            'it' => [
                'title' => 'Prenota un tavolo',
                'description' => 'Compila il form per inviare la tua richiesta di prenotazione.',
            ],
            'en' => [
                'title' => 'Book a table',
                'description' => 'Complete the form to send your booking request.',
            ],
            'de' => [
                'title' => 'Tisch reservieren',
                'description' => 'Füllen Sie das Formular aus, um Ihre Reservierungsanfrage zu senden.',
            ],
            'fr' => [
                'title' => 'Réserver une table',
                'description' => 'Remplissez le formulaire pour envoyer votre demande de réservation.',
            ],
            'es' => [
                'title' => 'Reservar una mesa',
                'description' => 'Completa el formulario para enviar tu solicitud de reserva.',
            ],
        ];

        $translations = collect($languages)->mapWithKeys(fn (string $language): array => [
            $language => $content[$language] ?? [
                'title' => 'Book a table',
                'description' => 'Complete the form to send your booking request.',
            ],
        ])->all();

        app(FormBlueprintService::class)->create([
            'type' => 'booking',
            'slug' => 'booking',
            'translations' => $translations,
            'enabled_languages' => $languages,
            'schedule' => null,
            'is_active' => true,
            'accepts_coupons' => true,
        ]);

        $this->command?->info('Default booking form created at /form/{language}/booking.');
    }
}

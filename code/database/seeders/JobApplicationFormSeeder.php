<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsDefaultMarketingForm;
use Illuminate\Database\Seeder;

class JobApplicationFormSeeder extends Seeder
{
    use SeedsDefaultMarketingForm;

    public function run(): void
    {
        $created = $this->seedDefaultForm('job-application', [
            'it' => ['title' => 'Lavora con noi', 'description' => 'Invia la tua candidatura per una posizione aperta.'],
            'en' => ['title' => 'Work with us', 'description' => 'Submit your application for an open position.'],
            'de' => ['title' => 'Arbeiten Sie mit uns', 'description' => 'Bewerben Sie sich auf eine offene Stelle.'],
            'fr' => ['title' => 'Rejoignez-nous', 'description' => 'Envoyez votre candidature pour un poste vacant.'],
            'es' => ['title' => 'Trabaja con nosotros', 'description' => 'Envía tu candidatura para un puesto vacante.'],
        ], [
            ['key' => 'phone', 'type' => 'tel', 'label' => ['it' => 'Telefono', 'en' => 'Phone', 'de' => 'Telefon', 'fr' => 'Téléphone', 'es' => 'Teléfono'], 'required' => true],
            ['key' => 'position', 'type' => 'text', 'label' => ['it' => 'Posizione desiderata', 'en' => 'Desired position', 'de' => 'Gewünschte Position', 'fr' => 'Poste souhaité', 'es' => 'Puesto deseado'], 'required' => true],
            ['key' => 'cover_letter', 'type' => 'textarea', 'label' => ['it' => 'Presentazione', 'en' => 'Cover letter', 'de' => 'Anschreiben', 'fr' => 'Lettre de motivation', 'es' => 'Carta de presentación'], 'required' => false],
            ['key' => 'cv', 'type' => 'file', 'label' => ['it' => 'Curriculum vitae', 'en' => 'Curriculum vitae', 'de' => 'Lebenslauf', 'fr' => 'Curriculum vitae', 'es' => 'Currículum vitae'], 'required' => true],
            ['key' => 'privacy_consent', 'type' => 'checkbox', 'label' => ['it' => 'Acconsento al trattamento dei dati personali', 'en' => 'I consent to the processing of my personal data', 'de' => 'Ich stimme der Verarbeitung meiner personenbezogenen Daten zu', 'fr' => 'Je consens au traitement de mes données personnelles', 'es' => 'Consiento el tratamiento de mis datos personales'], 'required' => true],
        ]);

        $this->command?->info($created ? 'Default job application form created.' : 'Job application form already exists; no changes were made.');
    }
}

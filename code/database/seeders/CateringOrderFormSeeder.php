<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsDefaultMarketingForm;
use Illuminate\Database\Seeder;

class CateringOrderFormSeeder extends Seeder
{
    use SeedsDefaultMarketingForm;

    public function run(): void
    {
        $created = $this->seedDefaultForm('catering-order', [
            'it' => ['title' => 'Richiedi un catering', 'description' => 'Raccontaci le esigenze del tuo evento e ti ricontatteremo con una proposta.'],
            'en' => ['title' => 'Request catering', 'description' => 'Tell us about your event and we will contact you with a proposal.'],
            'de' => ['title' => 'Catering anfragen', 'description' => 'Erzählen Sie uns von Ihrer Veranstaltung und wir senden Ihnen ein Angebot.'],
            'fr' => ['title' => 'Demander un service traiteur', 'description' => 'Décrivez-nous votre événement et nous vous recontacterons avec une proposition.'],
            'es' => ['title' => 'Solicitar catering', 'description' => 'Cuéntanos las necesidades de tu evento y te enviaremos una propuesta.'],
        ], [
            ['key' => 'phone', 'type' => 'tel', 'label' => ['it' => 'Telefono', 'en' => 'Phone', 'de' => 'Telefon', 'fr' => 'Téléphone', 'es' => 'Teléfono'], 'required' => true],
            ['key' => 'event_date', 'type' => 'date', 'label' => ['it' => 'Data evento', 'en' => 'Event date', 'de' => 'Veranstaltungsdatum', 'fr' => "Date de l'événement", 'es' => 'Fecha del evento'], 'required' => true],
            ['key' => 'guests', 'type' => 'number', 'label' => ['it' => 'Numero di persone', 'en' => 'Number of guests', 'de' => 'Anzahl der Gäste', 'fr' => 'Nombre de personnes', 'es' => 'Número de personas'], 'required' => true],
            ['key' => 'venue', 'type' => 'text', 'label' => ['it' => "Luogo dell'evento", 'en' => 'Event venue', 'de' => 'Veranstaltungsort', 'fr' => "Lieu de l'événement", 'es' => 'Lugar del evento'], 'required' => true],
            ['key' => 'service_type', 'type' => 'select', 'label' => ['it' => 'Tipo di servizio', 'en' => 'Service type', 'de' => 'Art des Services', 'fr' => 'Type de service', 'es' => 'Tipo de servicio'], 'options' => ['it' => ['Consegna', 'Buffet', 'Servizio al tavolo'], 'en' => ['Delivery', 'Buffet', 'Table service'], 'de' => ['Lieferung', 'Buffet', 'Tischservice'], 'fr' => ['Livraison', 'Buffet', 'Service à table'], 'es' => ['Entrega', 'Buffet', 'Servicio de mesa']], 'required' => true],
            ['key' => 'dietary_requirements', 'type' => 'textarea', 'label' => ['it' => 'Allergie ed esigenze alimentari', 'en' => 'Allergies and dietary requirements', 'de' => 'Allergien und Ernährungsbedürfnisse', 'fr' => 'Allergies et exigences alimentaires', 'es' => 'Alergias y necesidades alimentarias'], 'required' => false],
            ['key' => 'notes', 'type' => 'textarea', 'label' => ['it' => 'Dettagli della richiesta', 'en' => 'Request details', 'de' => 'Details der Anfrage', 'fr' => 'Détails de la demande', 'es' => 'Detalles de la solicitud'], 'required' => false],
        ]);

        $this->command?->info($created ? 'Default catering order form created.' : 'Catering order form already exists; no changes were made.');
    }
}

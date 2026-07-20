<?php

return [
    'datepicker' => env('MARKETING_FORMS_DATEPICKER', 'flatpickr'),
    'field_types' => ['text', 'textarea', 'email', 'tel', 'number', 'date', 'time', 'checkbox', 'radio', 'select', 'file'],
    'blueprints' => [
        'booking' => [
            'name' => 'Prenotazione tavolo',
            'fields' => [
                ['key' => 'name', 'type' => 'text', 'label' => ['it' => 'Nome e cognome', 'en' => 'Full name', 'de' => 'Vor- und Nachname'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'email', 'type' => 'email', 'label' => ['it' => 'Email', 'en' => 'Email', 'de' => 'E-Mail'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'phone', 'type' => 'tel', 'label' => ['it' => 'Telefono', 'en' => 'Phone', 'de' => 'Telefon'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'date', 'type' => 'date', 'label' => ['it' => 'Data', 'en' => 'Date', 'de' => 'Datum'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'time', 'type' => 'time', 'label' => ['it' => 'Orario', 'en' => 'Time', 'de' => 'Uhrzeit'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'guests', 'type' => 'number', 'label' => ['it' => 'Persone', 'en' => 'Guests', 'de' => 'Gäste'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'notes', 'type' => 'textarea', 'label' => ['it' => 'Note', 'en' => 'Notes', 'de' => 'Anmerkungen'], 'required' => false, 'visible' => true, 'locked' => false],
                ['key' => 'privacy_consent', 'type' => 'checkbox', 'label' => ['it' => 'Ho letto e accetto il documento: informativa sul trattamento dei dati personali', 'en' => 'I have read and accept the personal data processing policy', 'de' => 'Ich habe die Datenschutzinformation gelesen und akzeptiere sie'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'marketing_consent', 'type' => 'checkbox', 'label' => ['it' => 'Acconsento a ricevere comunicazioni di marketing, offerte speciali e promozioni in conformità con l’informativa sulla privacy.', 'en' => 'I consent to receive marketing communications, special offers and promotions in accordance with the privacy policy.', 'de' => 'Ich stimme dem Erhalt von Marketingmitteilungen, Sonderangeboten und Werbeaktionen gemäß der Datenschutzrichtlinie zu.'], 'required' => false, 'visible' => true, 'locked' => false],
            ],
        ],
        'event' => [
            'name' => 'Prenotazione evento',
            'fields_from' => 'booking',
        ],
        'generic' => [
            'name' => 'Form generico',
            'fields' => [
                ['key' => 'name', 'type' => 'text', 'label' => ['it' => 'Nome e cognome', 'en' => 'Full name', 'de' => 'Vor- und Nachname'], 'required' => true, 'visible' => true, 'locked' => false],
                ['key' => 'email', 'type' => 'email', 'label' => ['it' => 'Email', 'en' => 'Email', 'de' => 'E-Mail'], 'required' => true, 'visible' => true, 'locked' => false],
                ['key' => 'privacy_consent', 'type' => 'checkbox', 'label' => ['it' => 'Ho letto e accetto il documento: informativa sul trattamento dei dati personali', 'en' => 'I have read and accept the personal data processing policy', 'de' => 'Ich habe die Datenschutzinformation gelesen und akzeptiere sie'], 'required' => true, 'visible' => true, 'locked' => true],
                ['key' => 'marketing_consent', 'type' => 'checkbox', 'label' => ['it' => 'Acconsento a ricevere comunicazioni di marketing, offerte speciali e promozioni in conformità con l’informativa sulla privacy.', 'en' => 'I consent to receive marketing communications, special offers and promotions in accordance with the privacy policy.', 'de' => 'Ich stimme dem Erhalt von Marketingmitteilungen, Sonderangeboten und Werbeaktionen gemäß der Datenschutzrichtlinie zu.'], 'required' => false, 'visible' => true, 'locked' => false],
            ],
        ],
    ],
];

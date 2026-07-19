<?php

return [
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
            ],
        ],
    ],
];

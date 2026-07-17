<?php

return [
    'title' => 'Personale', 'add' => 'Aggiungi dipendente', 'edit' => 'Modifica dipendente',
    'new_user' => 'NUOVO UTENTE', 'edit_employee' => 'MODIFICA DIPENDENTE',
    'name' => 'Nome e cognome', 'phone' => 'Telefono', 'role' => 'Ruolo piattaforma',
    'select_role' => 'Seleziona ruolo', 'language' => 'Lingua',
    'whatsapp' => 'Può ricevere notifiche WhatsApp', 'telegram' => 'Può ricevere notifiche Telegram',
    'save_invite' => 'Salva e invia invito', 'sending' => 'Invio in corso…',
    'save' => 'Salva modifiche', 'saving' => 'Salvataggio…', 'cancel' => 'Annulla',
    'refresh' => 'Aggiorna', 'yes' => 'SÌ', 'no' => 'NO',
    'index' => [
        'search' => 'Cerca utente', 'actions' => 'Azioni', 'name' => 'Nome', 'status' => 'Stato',
        'role' => 'Ruolo', 'whatsapp_notifications' => 'Notifiche WhatsApp',
        'telegram_notifications' => 'Notifiche Telegram', 'active' => 'Attivo', 'invited' => 'Invitato',
        'delete' => 'Elimina', 'delete_confirmation' => 'Confermi di volere eliminare questo utente?',
        'empty' => 'Nessun utente trovato.',
    ],
    'permissions' => [
        'title' => 'Permessi personale', 'search' => 'Cerca dipendente', 'employee' => 'Dipendente',
        'empty' => 'Nessun dipendente trovato.', 'aria' => ':permission per :name',
    ],
    'roles' => ['admin' => 'Amministratore', 'manager' => 'Responsabile', 'operator' => 'Operatore', 'kiosk' => 'Chiosco'],
    'features' => [
        'ai' => 'Gestione AI', 'calendar' => 'Gestione Calendario', 'cash_register' => 'Gestione Cassa',
        'customers' => 'Gestione Clienti', 'orders' => 'Gestione Comande', 'marketing' => 'Gestione Marketing',
        'marketplace' => 'Gestione Marketplace', 'bookings' => 'Gestione Prenotazioni',
        'products' => 'Gestione Prodotti', 'rooms' => 'Gestione Sale/Tavoli', 'shifts' => 'Gestione Turni/Timesheet',
    ],
    'validation' => ['phone' => 'Inserisci il telefono in formato internazionale, ad esempio +393401234567.'],
    'messages' => [
        'created' => 'Dipendente creato. L’email di attivazione è stata inviata.', 'updated' => 'Dipendente aggiornato.',
        'deleted' => 'Utente eliminato.', 'cannot_delete_self' => 'Non puoi eliminare il tuo account.',
        'permission_updated' => 'Permesso aggiornato.',
    ],
];

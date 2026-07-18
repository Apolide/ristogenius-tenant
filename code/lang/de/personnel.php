<?php

return [
    'title' => 'Personal', 'add' => 'Mitarbeiter hinzufügen', 'edit' => 'Mitarbeiter bearbeiten',
    'new_user' => 'NEUER BENUTZER', 'edit_employee' => 'MITARBEITER BEARBEITEN',
    'name' => 'Vor- und Nachname', 'phone' => 'Telefon', 'role' => 'Plattformrolle',
    'select_role' => 'Rolle auswählen', 'language' => 'Sprache',
    'whatsapp' => 'Darf WhatsApp-Benachrichtigungen erhalten', 'telegram' => 'Darf Telegram-Benachrichtigungen erhalten',
    'save_invite' => 'Speichern und Einladung senden', 'sending' => 'Wird gesendet…',
    'save' => 'Änderungen speichern', 'saving' => 'Wird gespeichert…', 'cancel' => 'Abbrechen',
    'refresh' => 'Aktualisieren', 'yes' => 'JA', 'no' => 'NEIN',
    'index' => [
        'search' => 'Benutzer suchen', 'actions' => 'Aktionen', 'name' => 'Name', 'status' => 'Status',
        'role' => 'Rolle', 'whatsapp_notifications' => 'WhatsApp-Benachrichtigungen',
        'telegram_notifications' => 'Telegram-Benachrichtigungen', 'active' => 'Aktiv', 'invited' => 'Eingeladen',
        'delete' => 'Löschen', 'delete_confirmation' => 'Möchtest du diesen Benutzer wirklich löschen?',
        'empty' => 'Keine Benutzer gefunden.',
    ],
    'permissions' => [
        'title' => 'Personalberechtigungen', 'search' => 'Mitarbeiter suchen', 'employee' => 'Mitarbeiter',
        'empty' => 'Keine Mitarbeiter gefunden.', 'aria' => ':permission für :name',
    ],
    'roles' => ['admin' => 'Administrator', 'manager' => 'Manager', 'operator' => 'Bediener', 'kiosk' => 'Kiosk'],
    'features' => [
        'ai' => 'KI-Verwaltung', 'calendar' => 'Kalenderverwaltung', 'cash_register' => 'Kassenverwaltung',
        'customers' => 'Kundenverwaltung', 'orders' => 'Bestellverwaltung', 'marketing' => 'Marketingverwaltung',
        'marketplace' => 'Marketplace-Verwaltung', 'bookings' => 'Reservierungsverwaltung',
        'products' => 'Produktverwaltung', 'rooms' => 'Raum-/Tischverwaltung', 'shifts' => 'Schicht-/Stundenzettelverwaltung',
    ],
    'validation' => ['phone' => 'Gib die Telefonnummer im internationalen Format ein, zum Beispiel +393401234567.'],
    'messages' => [
        'created' => 'Mitarbeiter erstellt. Die Aktivierungs-E-Mail wurde gesendet.', 'updated' => 'Mitarbeiter aktualisiert.',
        'deleted' => 'Benutzer gelöscht.', 'cannot_delete_self' => 'Du kannst dein eigenes Konto nicht löschen.',
        'permission_updated' => 'Berechtigung aktualisiert.',
    ],
];

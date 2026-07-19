<?php

namespace App\Services\Settings;

use App\Models\TenantProfile;
use App\Services\CustomerLanguageService;

class TenantSettingMessageTemplatesService
{
    public const MESSAGE_TEMPLATE_DEFAULTS = [
        'booking_received' => [
            'label' => 'Nuova Prenotazione',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => ['subject' => '@@location_name@@ - Nuova prenotazione', 'message' => 'È stata inserita una nuova prenotazione di @@customer_name@@ per @@pax@@ persone il @@booking_date@@ alle @@booking_time@@.', 'additional_note' => '', 'sms' => 'Nuova prenotazione: @@customer_name@@, @@pax@@ persone, @@booking_date@@ @@booking_time@@.'],
                'en' => ['subject' => '@@location_name@@ - New booking', 'message' => 'A new booking has been created by @@customer_name@@ for @@pax@@ people on @@booking_date@@ at @@booking_time@@.', 'additional_note' => '', 'sms' => 'New booking: @@customer_name@@, @@pax@@ people, @@booking_date@@ @@booking_time@@.'],
                'de' => ['subject' => '@@location_name@@ - Neue Reservierung', 'message' => 'Eine neue Reservierung von @@customer_name@@ für @@pax@@ Personen am @@booking_date@@ um @@booking_time@@ wurde erstellt.', 'additional_note' => '', 'sms' => 'Neue Reservierung: @@customer_name@@, @@pax@@ Personen, @@booking_date@@ @@booking_time@@.'],
            ],
        ],
        'booking_edited_from_customer' => [
            'label' => 'Prenotazione Modificata dal Cliente',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => ['subject' => '@@location_name@@ - Prenotazione modificata dal cliente', 'message' => '@@customer_name@@ ha modificato la prenotazione per @@pax@@ persone del @@booking_date@@ alle @@booking_time@@. La prenotazione è in attesa di conferma.', 'additional_note' => '', 'sms' => 'Prenotazione modificata da @@customer_name@@: @@booking_date@@ @@booking_time@@, @@pax@@ persone.'],
                'en' => ['subject' => '@@location_name@@ - Booking modified by customer', 'message' => '@@customer_name@@ modified the booking for @@pax@@ people on @@booking_date@@ at @@booking_time@@. The booking is awaiting confirmation.', 'additional_note' => '', 'sms' => 'Booking modified by @@customer_name@@: @@booking_date@@ @@booking_time@@, @@pax@@ people.'],
                'de' => ['subject' => '@@location_name@@ - Reservierung vom Kunden geändert', 'message' => '@@customer_name@@ hat die Reservierung für @@pax@@ Personen am @@booking_date@@ um @@booking_time@@ geändert. Die Reservierung wartet auf Bestätigung.', 'additional_note' => '', 'sms' => 'Reservierung von @@customer_name@@ geändert: @@booking_date@@ @@booking_time@@, @@pax@@ Personen.'],
            ],
        ],
        'booking_proposal' => [
            'label' => 'Proposta modifica prenotazione',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => [
                    'subject' => '@@location_name@@ - Proposta modifica prenotazione',
                    'message' => "purtroppo la data e orario che hai scelto per la prenotazione da @@location_name@@ non e disponibile. Ti proponiamo un'alternativa: @@booking_date@@ alle @@booking_time@@. Ti preghiamo di accettare o rifiutare questa proposta tramite il bottone 'Rispondi alla proposta' qui in basso.",
                    'additional_note' => '',
                    'sms' => 'purtroppo la data e orario che hai scelto per la prenotazione da @@location_name@@ non e disponibile.',
                ],
                'en' => [
                    'subject' => '@@location_name@@ - Edit booking proposal',
                    'message' => "unfortunately the date and time you have chosen for the reservation from @@location_name@@ is not available. We are offering you an alternative: @@booking_date@@ at @@booking_time@@. Please accept or decline this proposal using the 'Reply to proposal' button below.",
                    'additional_note' => '',
                    'sms' => 'unfortunately the date and time you have chosen for the reservation from @@location_name@@ is not available.',
                ],
                'de' => [
                    'subject' => '@@location_name@@ - Buchungsvorschlag bearbeiten',
                    'message' => "Leider ist das von Ihnen gewählte Datum und die Uhrzeit für die Reservierung von @@location_name@@ nicht verfügbar. Wir bieten Ihnen eine Alternative an: @@booking_date@@ um @@booking_time@@. Bitte nehmen Sie diesen Vorschlag über die Schaltfläche 'Auf Vorschlag antworten' unten an oder lehnen Sie ihn ab.",
                    'additional_note' => '',
                    'sms' => 'Leider ist das von Ihnen gewählte Datum und die Uhrzeit für die Reservierung von @@location_name@@ nicht verfügbar.',
                ],
            ],
        ],
        'booking_remind' => [
            'label' => 'Remind prenotazione',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => [
                    'subject' => '@@location_name@@ - Promemoria',
                    'message' => "ti aspettiamo da @@location_name@@ il @@booking_date@@ alle @@booking_time@@. Non dimenticare di avvisarci se, per imprevisti, sei in ritardo oppure sei costretto ad annullare il tuo tavolo. E' molto importante per noi, te ne saremo grati!",
                    'additional_note' => '',
                    'sms' => 'ti aspettiamo da @@location_name@@ il @@booking_date@@ alle @@booking_time@@.',
                ],
                'en' => [
                    'subject' => '@@location_name@@ - Reminder',
                    'message' => 'we will be waiting for you at @@location_name@@ at @@booking_date@@ - @@booking_time@@. Please do not forget to warn us in case of unexpected circumstances, delays or if you are no longer able to come.',
                    'additional_note' => '',
                    'sms' => 'we will be waiting for you at @@booking_date@@ - @@booking_time@@.',
                ],
                'de' => [
                    'subject' => '@@location_name@@ - Erinnerung',
                    'message' => 'Wir erwarten Sie am @@booking_date@@ - @@booking_time@@ im @@location_name@@. Bitte informieren Sie uns, falls es zu unvorhergesehenen Ereignissen, Verspätungen oder Ihrer Verhinderung kommt.',
                    'additional_note' => '',
                    'sms' => 'Wir erwarten Sie am @@booking_date@@ - @@booking_time@@.',
                ],
            ],
        ],
        'booking_canceled' => [
            'label' => 'Prenotazione cancellata',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => [
                    'subject' => '@@location_name@@ - Prenotazione cancellata',
                    'message' => 'la prenotazione da @@location_name@@ per @@pax@@ persone il @@booking_date@@ @@booking_time@@ e stata cancellata.',
                    'additional_note' => '',
                    'sms' => 'Prenotazione da @@location_name@@ cancellata: @@booking_date@@ @@booking_time@@ per @@pax@@ persone.',
                ],
                'en' => [
                    'subject' => '@@location_name@@ - Booking canceled',
                    'message' => 'the reservation at @@location_name@@ for @@pax@@ people on @@booking_date@@ @@booking_time@@ has been cancelled.',
                    'additional_note' => '',
                    'sms' => 'Reservation at @@location_name@@ canceled: @@booking_date@@ @@booking_time@@ for @@pax@@ people.',
                ],
                'de' => [
                    'subject' => '@@location_name@@ - Buchung storniert',
                    'message' => 'Die Reservierung im @@location_name@@ für @@pax@@ Personen am @@booking_date@@ @@booking_time@@ wurde storniert.',
                    'additional_note' => '',
                    'sms' => 'Reservierung im @@location_name@@ storniert: @@booking_date@@ @@booking_time@@ für @@pax@@ Personen.',
                ],
            ],
        ],
        'booking_canceled_from_customer' => [
            'label' => 'Prenotazione cancellata dal Cliente',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => ['subject' => '@@location_name@@ - Prenotazione cancellata dal cliente', 'message' => '@@customer_name@@ ha cancellato la prenotazione per @@pax@@ persone del @@booking_date@@ alle @@booking_time@@.', 'additional_note' => '', 'sms' => 'Prenotazione cancellata da @@customer_name@@: @@booking_date@@ @@booking_time@@, @@pax@@ persone.'],
                'en' => ['subject' => '@@location_name@@ - Booking canceled by customer', 'message' => '@@customer_name@@ canceled the booking for @@pax@@ people on @@booking_date@@ at @@booking_time@@.', 'additional_note' => '', 'sms' => 'Booking canceled by @@customer_name@@: @@booking_date@@ @@booking_time@@, @@pax@@ people.'],
                'de' => ['subject' => '@@location_name@@ - Reservierung vom Kunden storniert', 'message' => '@@customer_name@@ hat die Reservierung für @@pax@@ Personen am @@booking_date@@ um @@booking_time@@ storniert.', 'additional_note' => '', 'sms' => 'Reservierung von @@customer_name@@ storniert: @@booking_date@@ @@booking_time@@, @@pax@@ Personen.'],
            ],
        ],
        'booking_denied' => [
            'label' => 'Prenotazione Rifiutata',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => [
                    'subject' => '@@location_name@@ - Ci dispiace, la tua prenotazione non e stata accettata',
                    'message' => 'purtroppo non possiamo accettare la tua prenotazione da @@location_name@@ per @@pax@@ persone il @@booking_date@@ @@booking_time@@.',
                    'additional_note' => '',
                    'sms' => 'Prenotazione da @@location_name@@ rifiutata: @@booking_date@@ per @@pax@@ persone.',
                ],
                'en' => [
                    'subject' => '@@location_name@@ - Sorry, your reservation has not been accepted',
                    'message' => 'unfortunately we cannot accept your reservation at @@location_name@@ for @@pax@@ people on @@booking_date@@ @@booking_time@@.',
                    'additional_note' => '',
                    'sms' => 'Reservation at @@location_name@@ rejected: @@booking_date@@ for @@pax@@ people.',
                ],
                'de' => [
                    'subject' => '@@location_name@@ - Ihre Reservierung konnte leider nicht angenommen werden.',
                    'message' => 'Leider können wir Ihre Reservierung für @@location_name@@ für @@pax@@ Personen am @@booking_date@@ @@booking_time@@ nicht annehmen.',
                    'additional_note' => '',
                    'sms' => 'Reservierung für @@location_name@@ abgelehnt: @@booking_date@@ für @@pax@@ Personen.',
                ],
            ],
        ],
        'booking_accepted' => [
            'label' => 'Prenotazione Accettata',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => [
                    'subject' => '@@location_name@@ - La tua prenotazione e stata accettata',
                    'message' => 'la tua prenotazione da @@location_name@@ per @@pax@@ persone e confermata il giorno @@booking_date@@ alle @@booking_time@@. Ti ricordiamo che puoi modificare o disdire in autonomia la tua prenotazione utilizzando il bottone Modifica prenotazione.',
                    'additional_note' => 'Ti ricordiamo che il sabato sera il tempo di permanenza massima al tavolo e di 90 minuti e che non possiamo garantire che il tavolo resti disponibile in caso di ritardo maggiore di 15 minuti.',
                    'sms' => 'Prenotazione da @@location_name@@ confermata per @@pax@@ persone il @@booking_date@@ alle @@booking_time@@',
                ],
                'en' => [
                    'subject' => '@@location_name@@ - Your booking has been accepted',
                    'message' => 'your reservation at @@location_name@@ for @@pax@@ people is confirmed on @@booking_date@@ at @@booking_time@@. We remind you that you can independently modify or cancel your reservation using the Modify reservation button.',
                    'additional_note' => 'We remind you that on Saturday evenings the maximum time you can stay at a table is 90 minutes and that we cannot guarantee that the table will remain available in the event of a delay of more than 15 minutes.',
                    'sms' => 'Reservation at @@location_name@@ confirmed for @@pax@@ people on @@booking_date@@ at @@booking_time@@',
                ],
                'de' => [
                    'subject' => '@@location_name@@ - Ihre Buchung wurde bestätigt',
                    'message' => 'Ihre Reservierung im @@location_name@@ für @@pax@@ Personen wurde am @@booking_date@@ um @@booking_time@@ bestätigt. Sie können Ihre Reservierung jederzeit über die Schaltfläche „Reservierung ändern“ anpassen oder stornieren.',
                    'additional_note' => 'Bitte beachten Sie, dass die maximale Aufenthaltsdauer an einem Tisch samstagsabends 90 Minuten beträgt und wir bei einer Verspätung von mehr als 15 Minuten nicht garantieren können, dass der Tisch frei bleibt.',
                    'sms' => 'Reservierung im @@location_name@@ für @@pax@@ Personen am @@booking_date@@ um @@booking_time@@ bestätigt',
                ],
            ],
        ],
        'booking_sent' => [
            'label' => 'Prenotazione Inviata',
            'wildcards' => ['@@location_name@@', '@@customer_name@@', '@@pax@@', '@@booking_date@@', '@@booking_time@@'],
            'translations' => [
                'it' => [
                    'subject' => '@@location_name@@ - La tua prenotazione e stata inviata',
                    'message' => 'la tua prenotazione da @@location_name@@ per @@pax@@ persone per il giorno @@booking_date@@ alle @@booking_time@@ e stata inviata. Ti ricordiamo che puoi modificare o disdire in autonomia la tua prenotazione utilizzando il bottone Modifica prenotazione.',
                    'additional_note' => '',
                    'sms' => 'Prenotazione da @@location_name@@ inviata per @@pax@@ persone il @@booking_date@@ alle @@booking_time@@',
                ],
                'en' => [
                    'subject' => '@@location_name@@ - Your booking has been sent',
                    'message' => 'your reservation at @@location_name@@ for @@pax@@ people on @@booking_date@@ at @@booking_time@@ has been sent. We remind you that you can independently modify or cancel your reservation using the Modify reservation button.',
                    'additional_note' => '',
                    'sms' => 'Reservation at @@location_name@@ sent for @@pax@@ people on @@booking_date@@ at @@booking_time@@',
                ],
                'de' => [
                    'subject' => '@@location_name@@ - Ihre Buchung wurde gesendet',
                    'message' => 'Ihre Reservierung im @@location_name@@ für @@pax@@ Personen am @@booking_date@@ um @@booking_time@@ wurde gesendet. Wir weisen Sie darauf hin, dass Sie Ihre Reservierung über die Schaltfläche "Reservierung ändern" selbstständig ändern oder stornieren können.',
                    'additional_note' => '',
                    'sms' => 'Reservierung im @@location_name@@ für @@pax@@ Personen am @@booking_date@@ um @@booking_time@@ gesendet',
                ],
            ],
        ],
    ];

    public function profile(): TenantProfile
    {
        return TenantProfile::query()->first()
            ?: TenantProfile::query()->create(['name' => config('tenant.name', config('app.name'))]);
    }

    public function tenantLanguages(): array
    {
        $languages = explode(',', (string) config('tenant.customer_languages', 'it,en'));
        $languages = array_values(array_unique(array_filter(array_map(
            fn (string $language) => strtolower(trim($language)),
            $languages
        ))));

        return $languages ?: ['it', 'en'];
    }

    public function languageMeta(): array
    {
        $languages = app(CustomerLanguageService::class);

        return collect($this->tenantLanguages())->mapWithKeys(
            fn (string $language): array => [$language => $languages->meta($language)]
        )->all();
    }

    public function messageTemplates(?string $search = null): array
    {
        $savedTemplates = $this->messageSettings()['templates'] ?? [];
        $templates = [];

        foreach (self::MESSAGE_TEMPLATE_DEFAULTS as $key => $defaultTemplate) {
            $templates[$key] = $this->normalizeMessageTemplate($key, $savedTemplates[$key] ?? []);
        }

        $search = trim((string) $search);

        if ($search === '') {
            return $templates;
        }

        return array_filter($templates, fn (array $template) => str_contains(strtolower($template['label']), strtolower($search)));
    }

    public function messageTemplate(string $key): ?array
    {
        return $this->messageTemplates()[$key] ?? null;
    }

    public function updateMessageTemplate(string $key, array $template): void
    {
        if (! isset(self::MESSAGE_TEMPLATE_DEFAULTS[$key])) {
            return;
        }

        $messageSettings = $this->messageSettings();
        $templates = $messageSettings['templates'] ?? [];
        $templates[$key] = $this->normalizeMessageTemplate($key, $template);
        $messageSettings['templates'] = $templates;

        $this->updateMessageSettings($messageSettings);
        $this->forgetLegacyMessageTemplate($key);
    }

    public function resetMessageTemplate(string $key): void
    {
        if (! isset(self::MESSAGE_TEMPLATE_DEFAULTS[$key])) {
            return;
        }

        $messageSettings = $this->messageSettings();
        $templates = $messageSettings['templates'] ?? [];
        unset($templates[$key]);
        $messageSettings['templates'] = $templates;

        $this->updateMessageSettings($messageSettings);
        $this->forgetLegacyMessageTemplate($key);
    }

    private function messageSettings(): array
    {
        $profile = $this->profile();
        $legacyTemplates = $profile->settings['messages']['templates'] ?? [];

        return array_replace_recursive(
            ['templates' => is_array($legacyTemplates) ? $legacyTemplates : []],
            $profile->message_settings ?? []
        );
    }

    private function updateMessageSettings(array $messageSettings): void
    {
        $this->profile()->update(['message_settings' => $messageSettings]);
    }

    private function forgetLegacyMessageTemplate(string $key): void
    {
        $profile = $this->profile();
        $settings = $profile->settings ?? [];

        if (! isset($settings['messages']['templates'][$key])) {
            return;
        }

        unset($settings['messages']['templates'][$key]);

        if (($settings['messages']['templates'] ?? []) === []) {
            unset($settings['messages']['templates']);
        }

        $profile->update(['settings' => $settings]);
    }

    private function normalizeMessageTemplate(string $key, array $savedTemplate): array
    {
        $defaultTemplate = self::MESSAGE_TEMPLATE_DEFAULTS[$key];
        $template = [
            'label' => __("message_settings.templates.{$key}"),
            'wildcards' => $defaultTemplate['wildcards'],
            'translations' => [],
        ];

        foreach ($this->tenantLanguages() as $language) {
            $defaultTranslation = $defaultTemplate['translations'][$language]
                ?? $defaultTemplate['translations']['en']
                ?? ['subject' => '', 'message' => '', 'additional_note' => '', 'sms' => ''];
            $savedTranslation = $savedTemplate['translations'][$language] ?? [];

            $template['translations'][$language] = [
                'subject' => (string) ($savedTranslation['subject'] ?? $defaultTranslation['subject'] ?? ''),
                'message' => (string) ($savedTranslation['message'] ?? $defaultTranslation['message'] ?? ''),
                'additional_note' => (string) ($savedTranslation['additional_note'] ?? $defaultTranslation['additional_note'] ?? ''),
                'sms' => (string) ($savedTranslation['sms'] ?? $defaultTranslation['sms'] ?? ''),
            ];
        }

        return $template;
    }
}

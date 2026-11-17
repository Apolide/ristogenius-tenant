<?php

namespace App\Support;

class UiLabels
{
    /** Translate protocol codes only at the presentation boundary. */
    public static function code(?string $value, string $group = 'statuses'): string
    {
        $labels = [
            'statuses' => [
                'draft' => 'Bozza', 'pending' => 'In attesa', 'processing' => 'In elaborazione',
                'in_flight' => 'Invio in corso', 'received' => 'Ricevuto', 'succeeded' => 'Completato',
                'issued' => 'Emesso', 'accepted' => 'Accettato', 'rejected' => 'Rifiutato',
                'invalid_format' => 'Formato non valido', 'unknown' => 'Esito da verificare', 'failed' => 'Non riuscito', 'cancelled' => 'Annullato',
                'active' => 'Attivo', 'inactive' => 'Non attivo', 'online' => 'Online', 'offline' => 'Offline',
                'ready' => 'Pronto', 'enabled' => 'Abilitata', 'disabled' => 'Disattivo',
            ],
            'methods' => ['cash' => 'Contanti', 'card' => 'Carta', 'voucher' => 'Buoni pasto', 'manual' => 'Manuale', 'mixed' => 'Misto'],
            'types' => ['receipt' => 'Documento commerciale', 'invoice' => 'Fattura', 'sale' => 'Vendita', 'return' => 'Reso', 'void' => 'Annullamento', 'credit_note' => 'Nota di credito'],
            'actions' => ['configure' => 'Configurazione', 'create_company' => 'Registrazione azienda', 'create_store' => 'Registrazione sede',
                'enable_invoices' => 'Abilitazione fatture', 'create_cashier' => 'Registrazione cassiere',
                'issue' => 'Emissione documento', 'send_invoice' => 'Invio fattura', 'return' => 'Reso', 'void' => 'Annullamento', 'close' => 'Chiusura fiscale'],
        ];

        return __($labels[$group][strtolower($value ?? '')] ?? ($value ?? ''));
    }

    /** Recognize built-in persisted errors from any supported language. External details remain verbatim. */
    public static function systemMessage(?string $message): string
    {
        if (! $message) {
            return '';
        }
        static $sources;
        if ($sources === null) {
            $sources = [];
            foreach (['en', 'de', 'it'] as $locale) {
                $path = lang_path($locale.'.json');
                foreach (is_file($path) ? json_decode(file_get_contents($path), true) : [] as $source => $translation) {
                    $sources[$translation] = $source;
                }
            }
        }

        return __($sources[$message] ?? $message);
    }

    public static function billItemName(string $id, string $name): string
    {
        return in_array($id, ['covers', 'service', 'delivery'], true) ? __($name) : $name;
    }
}

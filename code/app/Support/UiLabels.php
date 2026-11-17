<?php

namespace App\Support;

class UiLabels
{
    /** Translate protocol codes only at the presentation boundary. */
    public static function code(?string $value, string $group = 'statuses'): string
    {
        $labels = [
            'statuses' => [
                'draft' => 'cash_desk.fiscal_documents.draft', 'pending' => 'orders.pending', 'processing' => 'fiscal.processing',
                'in_flight' => 'cash_desk.sending', 'received' => 'fiscal.received', 'succeeded' => 'orders.completed',
                'issued' => 'cash_desk.issued', 'accepted' => 'orders.accepted', 'rejected' => 'cash_desk.fiscal_documents.rejected',
                'invalid_format' => 'fiscal.invalid_format', 'unknown' => 'fiscal.outcome_needs_verification', 'failed' => 'fiscal.failed', 'cancelled' => 'cash_desk.cancelled',
                'active' => 'fiscal.active', 'inactive' => 'fiscal.inactive', 'online' => 'fiscal.online', 'offline' => 'fiscal.offline',
                'ready' => 'cash_desk.bill.ready', 'enabled' => 'fiscal.enabled', 'disabled' => 'fiscal.disabled',
            ],
            'methods' => ['cash' => 'cash_desk.cash', 'card' => 'cash_desk.card', 'voucher' => 'cash_desk.meal_vouchers', 'manual' => 'cash_desk.manual', 'mixed' => 'cash_desk.bill.mixed'],
            'types' => ['receipt' => 'cash_desk.batch_documents.receipt', 'invoice' => 'cash_desk.fiscal_documents.invoice', 'sale' => 'cash_desk.fiscal_documents.sale', 'return' => 'cash_desk.fiscal_documents.return', 'void' => 'cash_desk.fiscal_documents.cancellation', 'credit_note' => 'cash_desk.credit_note'],
            'actions' => ['configure' => 'fiscal.configuration', 'create_company' => 'fiscal.company_registration', 'create_store' => 'fiscal.location_registration',
                'enable_invoices' => 'fiscal.invoice_activation', 'create_cashier' => 'fiscal.cashier_registration',
                'issue' => 'fiscal.document_issuance', 'send_invoice' => 'fiscal.invoice_submission', 'return' => 'cash_desk.fiscal_documents.return', 'void' => 'cash_desk.fiscal_documents.cancellation', 'close' => 'fiscal.fiscal_closing'],
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
                foreach (['cash_desk.messages', 'orders.messages', 'fiscal.messages'] as $group) {
                    foreach (__($group, [], $locale) as $key => $translation) {
                        $sources[$translation] = $group.'.'.$key;
                    }
                }
            }
        }

        return __($sources[$message] ?? $message);
    }

    public static function billItemName(string $id, string $name): string
    {
        return match ($id) {
            'covers' => __('cash_desk.cover_charge'),
            'service' => __('cash_desk.service'),
            'delivery' => __('cash_desk.delivery'),
            default => $name,
        };
    }
}

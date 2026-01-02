<?php

namespace App\Support\Phone;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

final class PhoneNormalizer
{
    /**
     * Normalizza un input in E.164 o ritorna null se non validabile.
     * - Accetta: "+39 333...", "0039 333...", "333...", "tel:+39..."
     * - Rimuove estensioni: "int. 123", "ext 123", "#123", "x123"
     */
    public static function toE164(?string $raw, string $defaultRegion = 'IT', bool $mobileOnly = false): ?string
    {
        $raw = self::preClean($raw);
        if ($raw === '') {
            return null;
        }

        // Se inizia con 00 => +
        if (str_starts_with($raw, '00')) {
            $raw = '+' . substr($raw, 2);
        }

        // Mantieni + solo se primo char, poi solo cifre
        $raw = self::keepLeadingPlusAndDigits($raw);

        // Hard safety: E.164 max 15 digits (escluso +) -> quindi max 16 chars con +
        // ma lasciamo un filo di margine prima del parse (es. utenti incollano roba).
        if (strlen($raw) > 25) {
            return null;
        }

        $util = PhoneNumberUtil::getInstance();

        try {
            $number = $util->parse($raw, $defaultRegion);
        } catch (NumberParseException) {
            return null;
        }

        if (! $util->isValidNumber($number)) {
            return null;
        }

        if ($mobileOnly) {
            $type = $util->getNumberType($number);
            $allowed = in_array($type, [PhoneNumberType::MOBILE, PhoneNumberType::FIXED_LINE_OR_MOBILE], true);
            if (! $allowed) {
                return null;
            }
        }

        $e164 = $util->format($number, \libphonenumber\PhoneNumberFormat::E164);

        // ulteriore guardrail
        if (!preg_match('/^\+[1-9]\d{6,14}$/', $e164)) { // min 7 digits totali (pratico)
            return null;
        }

        return $e164;
    }

    /**
     * Normalizza da due campi (prefisso + numero).
     * Se $phone contiene già + o 00, il prefisso viene ignorato.
     */
    public static function toE164FromParts(?string $prefix, ?string $phone, string $defaultRegion = 'IT', bool $mobileOnly = false): ?string
    {
        $prefix = self::keepLeadingPlusAndDigits(self::preClean($prefix));
        $phone  = self::preClean($phone);

        if ($phone === '') {
            return null;
        }

        // Se l'utente ha già messo un internazionale completo nel phone, usiamo quello.
        $phoneClean = self::keepLeadingPlusAndDigits($phone);
        if (str_starts_with($phoneClean, '+') || str_starts_with($phoneClean, '00')) {
            return self::toE164($phoneClean, $defaultRegion, $mobileOnly);
        }

        // Prefisso: consenti + e 1-3 cifre reali
        $prefixDigits = preg_replace('/\D/', '', (string) $prefix);
        if ($prefixDigits === '') {
            // fallback: se non c'è prefisso, prova col defaultRegion come nazionale
            return self::toE164($phoneClean, $defaultRegion, $mobileOnly);
        }

        if (strlen($prefixDigits) > 3) {
            return null;
        }

        $full = '+' . $prefixDigits . preg_replace('/\D/', '', $phoneClean);
        return self::toE164($full, $defaultRegion, $mobileOnly);
    }

    private static function preClean(?string $raw): string
    {
        $raw = trim((string) $raw);

        // gestisci "tel:+39..."
        $raw = preg_replace('/^\s*tel:\s*/i', '', $raw);

        // rimuovi estensioni finali: "ext 123", "int. 123", "#123", "x123"
        $raw = preg_replace('/\s*(ext\.?|int\.?|interno|x|#)\s*\d+\s*$/i', '', $raw);

        return trim($raw);
    }

    private static function keepLeadingPlusAndDigits(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $leadingPlus = ($value[0] === '+');

        // togli tutto ciò che non è cifra
        $digits = preg_replace('/\D/', '', $value) ?? '';

        return $leadingPlus ? ('+' . $digits) : $digits;
    }
}

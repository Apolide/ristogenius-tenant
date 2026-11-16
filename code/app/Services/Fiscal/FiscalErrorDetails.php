<?php

namespace App\Services\Fiscal;

final class FiscalErrorDetails
{
    /** Only error fields are retained; never headers, request data or full bodies. */
    public static function extract(?array $body, string $token): array
    {
        $code = $body['error'] ?? $body['code'] ?? null;
        $code = (is_string($code) || is_int($code)) && preg_match('/^[a-zA-Z0-9_.:-]{1,80}$/', (string) $code) && ($token === '' || ! str_contains((string) $code, $token)) ? (string) $code : null;
        $parts = [];
        $message = $body['message'] ?? null;
        if (is_string($message) && trim($message) !== '') {
            $parts[] = $message;
        }
        foreach (['errors', 'validation_errors'] as $field) {
            if (is_array($body[$field] ?? null)) {
                self::collect($body[$field], '', $parts);
            }
        }
        $text = implode(' · ', $parts);
        // Strip tags after removing credentials, in case a token contains markup.
        if ($token !== '') {
            $text = str_replace($token, '[REDACTED]', $text);
        }
        $text = preg_replace('/\bBearer\s+[^\s,;<>]+/i', 'Bearer [REDACTED]', $text);
        $text = preg_replace('/\b(authorization|token|api[_ -]?key|password|secret|pin)\b\s*[:=]\s*(?:"[^"]*"|\'[^\']*\'|[^\s,;<>]+)/i', '$1=[REDACTED]', $text);
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');

        return ['error_code' => $code, 'error_message' => $text === '' ? null : mb_substr($text, 0, 1000)];
    }

    private static function collect(array $errors, string $prefix, array &$parts, int $depth = 0): void
    {
        if ($depth > 4 || count($parts) >= 8) {
            return;
        }
        foreach ($errors as $key => $value) {
            if (preg_match('/(?:^|[._-])(?:authorization|token|api[_ -]?key|password|secret|pin)(?:$|[._-])/i', (string) $key)) {
                continue;
            }
            $path = is_int($key) ? $prefix : ltrim($prefix.'.'.$key, '.');
            if (is_string($value) && $value !== '') {
                $parts[] = ($path ? $path.': ' : '').$value;
            } elseif (is_array($value)) {
                self::collect($value, $path, $parts, $depth + 1);
            }
            if (count($parts) >= 8) {
                break;
            }
        }
    }
}

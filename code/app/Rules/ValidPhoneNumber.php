<?php

namespace App\Rules;

use App\Services\PhoneNumberService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

class ValidPhoneNumber implements ValidationRule
{
    public function __construct(private readonly string $region = 'IT') {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        try {
            app(PhoneNumberService::class)->normalize((string) $value, $this->region);
        } catch (InvalidArgumentException) {
            $message = match (app()->getLocale()) {
                'it' => 'Il numero di telefono non è valido per il Paese selezionato.',
                'de' => 'Die Telefonnummer ist für das ausgewählte Land nicht gültig.',
                default => 'The phone number is not valid for the selected country.',
            };
            $fail($message);
        }
    }
}

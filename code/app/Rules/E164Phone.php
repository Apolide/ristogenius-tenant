<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Support\Phone\PhoneNormalizer;

final class E164Phone implements ValidationRule
{
    public function __construct(
        private readonly string $defaultRegion = 'IT',
        private readonly bool $mobileOnly = false
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $e164 = PhoneNormalizer::toE164((string) $value, $this->defaultRegion, $this->mobileOnly);

        if ($e164 === null) {
            $fail('Numero di telefono non valido.');
        }
    }
}

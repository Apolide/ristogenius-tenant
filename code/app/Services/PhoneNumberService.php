<?php

namespace App\Services;

use InvalidArgumentException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberService
{
    public function normalize(?string $value, string $region = 'IT'): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (! preg_match('/^\+?[0-9\s().\/-]+$/u', $value)) {
            throw new InvalidArgumentException('invalid_characters');
        }

        try {
            $phone = PhoneNumberUtil::getInstance();
            $number = $phone->parse($value, strtoupper($region));
        } catch (NumberParseException) {
            throw new InvalidArgumentException('invalid_number');
        }

        if (! $phone->isValidNumber($number)) {
            throw new InvalidArgumentException('invalid_number');
        }

        return $phone->format($number, PhoneNumberFormat::E164);
    }

    public function split(?string $value, string $fallbackRegion = 'IT'): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return ['region' => strtoupper($fallbackRegion), 'national' => ''];
        }

        try {
            $phone = PhoneNumberUtil::getInstance();
            $number = $phone->parse($value, strtoupper($fallbackRegion));
            $region = $phone->getRegionCodeForNumber($number) ?: strtoupper($fallbackRegion);

            return [
                'region' => $region,
                'national' => $phone->format($number, PhoneNumberFormat::NATIONAL),
            ];
        } catch (NumberParseException) {
            return ['region' => strtoupper($fallbackRegion), 'national' => $value];
        }
    }
}

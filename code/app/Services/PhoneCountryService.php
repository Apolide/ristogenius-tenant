<?php

namespace App\Services;

use libphonenumber\PhoneNumberUtil;
use Giggsey\Locale\Locale;

class PhoneCountryService
{
    public function countries(): array
    {
        $phone = PhoneNumberUtil::getInstance();
        $locale = app()->getLocale();
        return collect($phone->getSupportedRegions())->map(function (string $region) use ($phone, $locale): array {
            $name = Locale::getDisplayRegion('-'.$region, $locale)
                ?: Locale::getDisplayRegion('-'.$region, 'en')
                ?: $region;

            return [
                'region' => $region,
                'name' => $name,
                'prefix' => '+'.$phone->getCountryCodeForRegion($region),
                'flag' => $this->flag($region),
            ];
        })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }

    private function flag(string $region): string
    {
        return mb_chr(127397 + ord($region[0])).mb_chr(127397 + ord($region[1]));
    }
}

<?php

namespace Tests\Unit;

use App\Services\PhoneCountryService;
use Tests\TestCase;

class PhoneCountryServiceTest extends TestCase
{
    public function test_it_returns_localized_countries_with_prefix_and_flag(): void
    {
        app()->setLocale('it');
        $countries = app(PhoneCountryService::class)->countries();
        $italy = collect($countries)->firstWhere('region', 'IT');

        $this->assertGreaterThan(200, count($countries));
        $this->assertSame('+39', $italy['prefix']);
        $this->assertSame('🇮🇹', $italy['flag']);
        $this->assertSame('Italia', $italy['name']);
    }

    public function test_countries_are_sorted_by_display_name(): void
    {
        $names = array_column(app(PhoneCountryService::class)->countries(), 'name');
        $sorted = $names;
        sort($sorted, SORT_NATURAL | SORT_FLAG_CASE);

        $this->assertSame($sorted, $names);
    }
}

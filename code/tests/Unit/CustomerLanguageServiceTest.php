<?php

namespace Tests\Unit;

use App\Services\CustomerLanguageService;
use Tests\TestCase;

class CustomerLanguageServiceTest extends TestCase
{
    public function test_it_returns_only_configured_customer_languages_with_metadata(): void
    {
        config()->set('tenant.customer_languages', 'it,en,de');

        $languages = app(CustomerLanguageService::class)->enabled();

        $this->assertSame(['it', 'en', 'de'], array_keys($languages));
        $this->assertSame(['label' => 'Italiano', 'flag' => '🇮🇹'], $languages['it']);
        $this->assertSame('🇬🇧', $languages['en']['flag']);
    }

    public function test_it_provides_safe_metadata_for_an_unknown_language(): void
    {
        $this->assertSame(
            ['label' => 'PT', 'flag' => '🌐'],
            app(CustomerLanguageService::class)->meta('pt')
        );
    }
}

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

    public function test_public_and_backend_language_lists_are_independent_and_use_the_shared_catalog(): void
    {
        config([
            'tenant.customer_languages' => 'EN, gr, es, en, xx',
            'tenant.backend_languages' => 'it, gr, fr',
        ]);

        $languages = app(CustomerLanguageService::class);

        $this->assertSame(['en', 'gr', 'es'], array_keys($languages->enabled()));
        $this->assertSame(['it', 'gr', 'fr'], array_keys($languages->backend()));
        $this->assertSame(['label' => 'Ελληνικά', 'flag' => '🇬🇷'], $languages->enabled()['gr']);
        $this->assertSame($languages->enabled()['gr'], $languages->backend()['gr']);
        $this->assertSame('el', $languages->htmlLocale('gr'));
    }

    public function test_it_provides_safe_metadata_for_an_unknown_language(): void
    {
        $this->assertSame(
            ['label' => 'PT', 'flag' => '🌐'],
            app(CustomerLanguageService::class)->meta('pt')
        );
    }
}

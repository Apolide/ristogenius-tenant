<?php

namespace Tests\Feature\Marketing;

use App\Mail\MarketingMail;
use App\Models\Coupon;
use App\Models\CouponAssignment;
use App\Models\MessageOutbox;
use App\Services\Marketing\CouponService;
use App\Services\TenantBrandingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CouponPublicPresentationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['tenant.customer_languages' => 'it,en,de']);
        $this->mock(TenantBrandingService::class, function ($mock) {
            $mock->shouldReceive('branding')->andReturn([
                'name' => 'Ristorante di Test', 'address' => 'Via Roma 1',
                'logo_url' => 'https://example.test/logo.png',
            ]);
        });
    }

    private function coupon(): Coupon
    {
        return Coupon::create([
            'name' => 'Friends', 'slug' => (string) Str::uuid(), 'enabled_languages' => ['it', 'en', 'de'],
            'translations' => [
                'it' => ['title' => 'Birra tra amici', 'description' => '<p>FULL-DESCRIPTION</p>', 'terms' => '<p>FULL-TERMS</p>'],
                'en' => ['title' => 'Beer with friends', 'description' => '<p>Offer</p>', 'terms' => 'Terms'],
                'de' => ['title' => 'Bier mit Freunden', 'description' => '<p>Angebot</p>', 'terms' => 'Bedingungen'],
            ],
            'rules' => ['min_pax' => 2, 'max_uses' => 1, 'weekdays' => []],
            'benefit' => ['type' => 'percent', 'value' => 1000], 'fields' => CouponService::fields(),
            'valid_until' => today()->addMonth(), 'is_active' => true,
        ]);
    }

    private function requestCoupon(Coupon $coupon): array
    {
        app(CouponService::class)->request($coupon, [
            'name' => 'Ada <&>', 'email' => 'ada@example.test',
            'privacy_consent' => true, 'marketing_consent' => false,
        ], 'it');

        return MessageOutbox::where('deduplication_key', 'like', 'coupon-claim:%')->sole()->payload;
    }

    public function test_confirmation_email_identifies_the_offer_and_restaurant_in_the_requested_language(): void
    {
        app()->setLocale('en');
        $content = $this->requestCoupon($this->coupon());
        $this->assertSame('Ottieni il coupon Birra tra amici – Ristorante di Test', $content['subject']);
        $html = (new MarketingMail($content))->locale('it')->render();
        foreach (['Birra tra amici', 'Ristorante di Test', '10%', '30 minuti', 'Via Roma 1', 'Ada &lt;&amp;&gt;'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('FULL-DESCRIPTION', $html);
        $this->assertStringNotContainsString('FULL-TERMS', $html);
        $text = view('marketing.email-text', ['content' => $content])->render();
        $this->assertStringContainsString('10%', $text);
        $this->assertStringContainsString($content['url'], $text);
        $this->assertDatabaseCount('coupon_assignments', 0);
    }

    public function test_coupon_pages_share_branding_and_language_switch_preserves_the_claim_and_assignment_tokens(): void
    {
        $coupon = $this->coupon();
        $content = $this->requestCoupon($coupon);
        $token = basename(parse_url($content['url'], PHP_URL_PATH));
        foreach ([
            route('coupons.public', ['language' => 'it', 'coupon' => $coupon->slug]),
            route('coupons.verify', ['language' => 'en', 'token' => $token]),
        ] as $url) {
            $this->get($url)->assertOk()->assertSee('tenant-public-form-header', false)
                ->assertSee('tenant-public-form-footer', false)->assertSee('service-language', false)
                ->assertSee('Ristorante di Test')->assertSee('Via Roma 1')->assertSee('RistoGenius');
        }
        $this->get(route('coupons.verify', ['language' => 'de', 'token' => $token]))
            ->assertOk()->assertSee('Bier mit Freunden')->assertSee('lang="de"', false)
            ->assertSee(route('coupons.confirm', ['language' => 'de', 'token' => $token]), false);
        $this->assertDatabaseCount('coupon_assignments', 0);
        $this->post(route('coupons.confirm', ['language' => 'en', 'token' => $token]))->assertRedirect();
        $assignment = CouponAssignment::sole();
        $this->get($assignment->url('en'))->assertOk()->assertSee('Beer with friends')
            ->assertSee('tenant-public-form-footer', false)->assertSee($assignment->url('de'), false);
    }

    public function test_coupon_download_is_a_single_a6_pdf_with_a_descriptive_filename(): void
    {
        $coupon = $this->coupon();
        $coupon->update(['slug' => 'birra-tra-amici']);
        $content = $this->requestCoupon($coupon);
        $token = basename(parse_url($content['url'], PHP_URL_PATH));
        $assignment = app(CouponService::class)->confirm($token);
        $this->get($assignment->url('it'))->assertOk()->assertSee(route('coupons.pdf', ['language' => 'it', 'token' => $assignment->access_token]), false);
        $response = $this->get(route('coupons.pdf', ['language' => 'it', 'token' => $assignment->access_token]))
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="ristorante-di-test-coupon-birra-tra-amici.pdf"')
            ->assertHeader('Referrer-Policy', 'no-referrer');
        $pdf = $response->getContent();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertSame(1, preg_match_all('~/Type\s*/Page\b~', $pdf));
        $this->assertMatchesRegularExpression('~/MediaBox\s*\[0\.000 0\.000 297\.640 419\.530\]~', $pdf);
        $this->assertStringContainsString($assignment->url('it'), $pdf);
        $this->get(route('coupons.pdf', ['language' => 'it', 'token' => Str::random(64)]))->assertNotFound();
    }

    public static function transparentLogoFormats(): array
    {
        return [['png'], ['webp']];
    }

    #[DataProvider('transparentLogoFormats')]
    public function test_pdf_flattens_transparent_logos_on_white_and_fits_weekday_rules_on_one_page(string $format): void
    {
        $coupon = $this->coupon();
        $coupon->update([
            'valid_from' => today()->addDays(2),
            'rules' => ['min_pax' => 4, 'max_pax' => 12, 'max_uses' => null, 'weekdays' => [1, 2, 3, 4]],
            'benefit' => ['type' => 'gift', 'quantity' => 1, 'quantity_basis' => 'fixed'],
        ]);
        $content = $this->requestCoupon($coupon);
        $token = basename(parse_url($content['url'], PHP_URL_PATH));
        $assignment = app(CouponService::class)->confirm($token);
        $assignment->uses_count = 2;
        $path = public_path('coupon-pdf-test-logo.'.$format);
        $image = imagecreatetruecolor(300, 120);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, 100, 40, 200, 80, imagecolorallocatealpha($image, 200, 20, 20, 0));
        $format === 'webp' ? imagewebp($image, $path, 100) : imagepng($image, $path);
        imagedestroy($image);
        $embeddedLogo = null;
        \Illuminate\Support\Facades\View::composer('marketing.coupon-pdf', function ($view) use (&$embeddedLogo) {
            $embeddedLogo = $view->getData()['logo'];
        });
        try {
            foreach (['it', 'en', 'de'] as $language) {
                $pdf = app(\App\Services\Marketing\CouponPdf::class)->render($assignment, $language, [
                    'name' => 'Test Restaurant', 'address' => 'Via Roma 1',
                    'logo_url' => url('coupon-pdf-test-logo.'.$format),
                ]);
                $this->assertSame(1, preg_match_all('~/Type\s*/Page\b~', $pdf), $language.' '.$format);
                $this->assertStringContainsString('/Subtype /Image', $pdf);
                $this->assertNotNull($embeddedLogo);
                $logo = imagecreatefromstring(base64_decode(explode(',', $embeddedLogo, 2)[1]));
                $corner = imagecolorsforindex($logo, imagecolorat($logo, 0, 0));
                $this->assertSame(['red' => 255, 'green' => 255, 'blue' => 255, 'alpha' => 0], $corner);
                $center = imagecolorsforindex($logo, imagecolorat($logo, 150, 60));
                $this->assertGreaterThan(150, $center['red']);
                $this->assertLessThan(50, $center['green']);
                imagedestroy($logo);
            }
        } finally {
            @unlink($path);
        }
    }

    public function test_verify_and_confirm_reject_languages_disabled_for_the_coupon(): void
    {
        $coupon = $this->coupon();
        $coupon->update(['enabled_languages' => ['it']]);
        $content = $this->requestCoupon($coupon);
        $token = basename(parse_url($content['url'], PHP_URL_PATH));
        $this->get(route('coupons.verify', ['language' => 'en', 'token' => $token]))->assertNotFound();
        $this->post(route('coupons.confirm', ['language' => 'en', 'token' => $token]))->assertNotFound();
        $this->assertDatabaseCount('coupon_assignments', 0);
    }
}

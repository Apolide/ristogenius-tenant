<?php

namespace App\Http\Controllers;

use App\Models\CampaignDelivery;
use App\Models\CouponClaim;
use App\Models\MarketingPreference;
use App\Services\Marketing\CampaignService;
use App\Services\Marketing\CouponService;
use Illuminate\Http\Request;

class MarketingPublicController extends Controller
{
    private function locale(string $language): void
    {
        abort_unless(array_key_exists($language, app(\App\Services\CustomerLanguageService::class)->enabled()), 404);
        app()->setLocale($language);
    }

    public function verify(string $language, string $token)
    {
        $claim = CouponClaim::where('token_hash', hash('sha256', $token))->firstOrFail();
        $this->locale($language);
        abort_unless(in_array($language, $claim->coupon->enabled_languages, true), 404);
        abort_if($claim->expires_at->isPast(), 410);

        return response()->view('marketing.verify', ['token' => $token, 'language' => $language, 'claim' => $claim, 'branding' => app(\App\Services\TenantBrandingService::class)->branding()])->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer');
    }

    public function confirm(Request $request, string $language, string $token)
    {
        $this->locale($language);
        $claim = CouponClaim::where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_unless(in_array($language, $claim->coupon->enabled_languages, true), 404);
        $answers = $request->post('answers');
        abort_unless($answers === null || is_array($answers), 422);
        $assignment = app(CouponService::class)->confirm($token, $answers, $language);

        return redirect($assignment->url($language));
    }

    public function card(string $language, string $token)
    {
        $assignment = app(CouponService::class)->fromToken($token);
        $this->locale($language);
        abort_unless(in_array($language, $assignment->snapshot['enabled_languages'], true), 404);

        return response()->view('marketing.coupon-card', compact('assignment', 'language'))->header('Cache-Control', 'no-store')->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function pdf(string $language, string $token)
    {
        $assignment = app(CouponService::class)->fromToken($token);
        $this->locale($language);
        abort_unless(in_array($language, $assignment->snapshot['enabled_languages'], true), 404);
        $branding = app(\App\Services\TenantBrandingService::class)->branding();
        $pdf = app(\App\Services\Marketing\CouponPdf::class);

        return response($pdf->render($assignment, $language, $branding))->withHeaders([
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf->filename($assignment, $branding, $language).'"',
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    public function image(Request $request, string $language, string $token)
    {
        $assignment = app(CouponService::class)->fromToken($token);
        $this->locale($language);
        abort_unless(in_array($language, $assignment->snapshot['enabled_languages'], true), 404);
        $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(new \BaconQrCode\Renderer\RendererStyle\RendererStyle(240, 15), new \BaconQrCode\Renderer\Image\SvgImageBackEnd));
        $qr = preg_replace('/<\?xml[^>]*\?>/', '', $writer->writeString($assignment->url($language)));
        $qr = preg_replace('/<svg /', '<svg x="230" y="120" ', $qr, 1);
        $title = htmlspecialchars(mb_substr($assignment->snapshot['translations'][$language]['title'] ?? $assignment->snapshot['name'], 0, 55), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $code = htmlspecialchars($assignment->id, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="700" height="460" viewBox="0 0 700 460"><rect width="700" height="460" fill="white"/><text x="350" y="70" text-anchor="middle" font-family="Arial" font-size="24">'.$title.'</text>'.$qr.'<text x="350" y="410" text-anchor="middle" font-family="Arial" font-size="15">'.$code.'</text></svg>';

        return response($svg)->header('Content-Type', 'image/svg+xml')->header('Cache-Control', 'no-store')->header('Content-Disposition', ($request->boolean('download') ? 'attachment' : 'inline').'; filename="coupon.svg"');
    }

    public function click(Request $request, CampaignDelivery $delivery)
    {
        abort_unless($request->hasValidSignature(), 403);
        $this->locale($delivery->language);
        if (! $delivery->clicked_at) {
            $delivery->update(['clicked_at' => now()]);
        }
        session(['marketing.delivery' => $delivery->id]);
        $url = app(CampaignService::class)->action($delivery->run->snapshot, $delivery->language, $delivery);
        abort_unless($url, 404);

        return redirect($url)->header('Referrer-Policy', 'no-referrer');
    }

    public function unsubscribe(Request $request, string $language, string $token)
    {
        $this->locale($language);
        $preference = MarketingPreference::where('token_hash', hash('sha256', $token))->firstOrFail();
        if ($request->isMethod('post')) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($preference) {
                $preference->update(['unsubscribed_at' => now()]);
                \App\Models\Customer::whereKey($preference->customer_id)->update(['consent_marketing' => false]);
                \Illuminate\Support\Facades\DB::table('marketing_consent_events')->insert(['customer_id' => $preference->customer_id, 'consent' => false, 'source' => 'unsubscribe', 'policy_version' => '1', 'recorded_at' => now()]);
            });
        }

        return response()->view('marketing.unsubscribe', ['done' => (bool) $preference->unsubscribed_at, 'language' => $language, 'token' => $token])->header('Cache-Control', 'no-store');
    }
}

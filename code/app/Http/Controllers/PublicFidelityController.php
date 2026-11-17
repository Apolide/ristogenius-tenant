<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FidelityCard;
use App\Models\FidelityMovement;
use App\Models\FidelityProgram;
use App\Models\FidelityReward;
use App\Services\CustomerLanguageService;
use App\Services\Fidelity\FidelityMail;
use App\Services\Fidelity\FidelityPdf;
use App\Services\Marketing\MarketingMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicFidelityController extends Controller
{
    public function show(Request $request)
    {
        $program = FidelityProgram::findOrFail(1);
        $languages = $this->languages();
        $languageUrls = collect($languages)->mapWithKeys(fn ($meta, $code) => [$code => route('fidelity.subscribe', ['language' => $code])])->all();
        $language = $request->query('language');
        $language = is_string($language) && isset($languages[$language]) ? $language : app(CustomerLanguageService::class)->default();
        app()->setLocale($language);
        $intro = app(FidelityMail::class)->copy($program->copyFor('form', $language) ?? __('fidelity.intro'), $program);

        return view('fidelity.subscribe', compact('intro', 'language', 'languageUrls', 'program'));
    }

    public function subscribe(Request $request)
    {
        abort_unless(FidelityProgram::findOrFail(1)->active, 404);
        $data = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'email' => 'required|email:rfc|max:254',
            'language' => ['required', Rule::in(array_keys($this->languages()))],
            'privacy' => 'accepted',
        ]);
        $data['email'] = Str::lower(trim($data['email']));
        $data['ip'] = $request->ip();
        $key = Str::uuid()->toString();
        $token = Crypt::encryptString(json_encode($data, JSON_THROW_ON_ERROR));
        $url = URL::temporarySignedRoute('fidelity.confirm', now()->addDay(), ['token' => $token]);
        app(MarketingMailer::class)->enqueue('fidelity-confirm:'.$key, $data['email'], $data['language'],
            __('fidelity.confirm_title', [], $data['language']), __('fidelity.confirm_email', [], $data['language']), $url,
            ['button' => __('fidelity.confirm_title', [], $data['language'])]);

        return redirect()->route('fidelity.subscribe', ['language' => $data['language']])->with('fidelity_sent', true);
    }

    public function confirm(Request $request)
    {
        $url = $request->fullUrl();
        $data = $this->confirmation($request);
        $language = $data['language'];

        return $this->privatePage('fidelity.confirm', compact('url', 'language'));
    }

    public function activate(Request $request)
    {
        $data = $this->confirmation($request);
        $card = DB::transaction(function () use ($data) {
            $program = FidelityProgram::lockForUpdate()->findOrFail(1);
            abort_unless($program->active, 404);
            $customer = Customer::where('email', $data['email'])->lockForUpdate()->first()
                ?? Customer::create([
                    'firstname' => $data['firstname'], 'lastname' => $data['lastname'],
                    'display_name' => $data['firstname'].' '.$data['lastname'], 'email' => $data['email'],
                    'registration_source' => 'fidelity', 'lang' => $data['language'],
                ]);
            if (! $customer->consent_privacy) {
                $customer->update(['consent_privacy' => true, 'datetime_consent_privacy' => now(), 'ip_consent_privacy' => $data['ip']]);
            }
            $card = $customer->fidelityCard()->firstOrCreate([], [
                'token' => Str::random(64), 'language' => $data['language'], 'points' => $program->starting_points,
            ]);
            if ($card->wasRecentlyCreated) {
                $reference = 'activation:'.$card->id;
                FidelityMovement::create(['fidelity_card_id' => $card->id, 'reference' => $reference, 'points' => $program->starting_points]);
                app(FidelityMail::class)->notify($card, $program, 'activation', $reference);
            }

            return $card;
        }, 3);

        return redirect()->to($card->statusUrl())->header('Referrer-Policy', 'no-referrer');
    }

    public function status(string $token)
    {
        $card = FidelityCard::where('token', $token)->firstOrFail();
        abort_unless(hash_equals($card->token, $token), 404);
        $program = FidelityProgram::findOrFail(1);
        $language = $card->language;
        app()->setLocale($language);
        $rewards = $program->active ? FidelityReward::orderBy('points')->orderBy('id')->get() : collect();

        $bookingUrl = $program->bookingUrl($language);

        return $this->privatePage('fidelity.status', compact('card', 'program', 'language', 'rewards', 'bookingUrl'));
    }

    public function pdf(string $token, FidelityPdf $pdf)
    {
        $card = FidelityCard::where('token', $token)->firstOrFail();
        abort_unless(hash_equals($card->token, $token), 404);

        return response($pdf->render($card), 200, [
            'Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="fidelity-card.pdf"',
            'Cache-Control' => 'private, no-store', 'Referrer-Policy' => 'no-referrer',
        ]);
    }

    private function confirmation(Request $request): array
    {
        abort_unless(FidelityProgram::findOrFail(1)->active, 404);
        try {
            $data = json_decode(Crypt::decryptString((string) $request->query('token')), true, flags: JSON_THROW_ON_ERROR);
        } catch (\Illuminate\Contracts\Encryption\DecryptException|\JsonException) {
            abort(404);
        }
        app()->setLocale($data['language']);

        return $data;
    }

    private function languages(): array
    {
        $languages = app(CustomerLanguageService::class);

        return $languages->enabled() ?: [$languages->default() => $languages->meta($languages->default())];
    }

    private function privatePage(string $view, array $data)
    {
        return response()->view($view, $data)->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer')->header('X-Robots-Tag', 'noindex, nofollow');
    }
}

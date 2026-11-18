<?php

namespace App\Http\Controllers;

use App\Services\CustomerLanguageService;
use App\Services\GiftCards\GiftCardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicGiftCardController extends Controller
{
    public function show(Request $request, CustomerLanguageService $languages, ?string $language = null): View
    {
        $languageUrls = $this->languageUrls($languages);
        $language ??= array_key_first($languageUrls);
        abort_unless(isset($languageUrls[$language]), 404);
        app()->setLocale($language);

        return view('gift-cards.public', [
            'language' => $language,
            'languageUrls' => $languageUrls,
            'confirmation' => $request->session()->get('gift_card_confirmation'),
        ]);
    }

    public function store(Request $request, CustomerLanguageService $languages, GiftCardService $cards, ?string $language = null): RedirectResponse
    {
        $languageUrls = $this->languageUrls($languages);
        $language ??= array_key_first($languageUrls);
        abort_unless(isset($languageUrls[$language]), 404);
        app()->setLocale($language);

        $data = $request->validate([
            'sender_firstname' => ['required', 'string', 'max:100'],
            'sender_lastname' => ['required', 'string', 'max:100'],
            'sender_email' => ['required', 'email:rfc', 'max:254'],
            'sender_phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9 ().-]{5,29}$/D'],
            'recipient_firstname' => ['required', 'string', 'max:100'],
            'recipient_lastname' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ().-]{5,29}$/D'],
            'send_to_recipient' => ['required', 'boolean'],
            'recipient_email' => [Rule::excludeIf(fn () => ! $request->boolean('send_to_recipient')), 'required', 'email:rfc', 'max:254'],
            'dedication' => ['nullable', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'between:20,999999.99', 'regex:/^[0-9]{1,6}(?:\.[0-9]{1,2})?$/D'],
            'privacy' => ['accepted'],
        ], [], __('gift_cards.fields'));

        $card = $cards->request($data, $language);

        return redirect()->route('gift-cards.public', $language)->with('gift_card_confirmation', [
            'reference' => $card->reference,
            'amount' => $card->formattedAmount($language),
        ]);
    }

    private function languageUrls(CustomerLanguageService $languages): array
    {
        $enabled = $languages->enabled();

        return array_map(
            fn (string $language) => route('gift-cards.public', $language),
            array_combine(array_keys($enabled), array_keys($enabled)),
        ) ?: ['it' => route('gift-cards.public', 'it')];
    }
}

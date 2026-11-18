<?php

namespace App\Services\Marketing;

use App\Models\Coupon;
use App\Models\CouponClaim;
use App\Models\Customer;
use App\Services\PhoneNumberService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponClaimFlow
{
    public function claim(string $token, string $language): CouponClaim
    {
        abort_unless(strlen($token) === 64, 404);
        $claim = CouponClaim::with('coupon')->where('token_hash', hash('sha256', $token))->firstOrFail();
        abort_if($claim->expires_at->isPast(), 410);
        abort_unless(in_array($language, $claim->coupon->enabled_languages, true)
            && array_key_exists($language, app(\App\Services\CustomerLanguageService::class)->enabled()), 404);

        return $claim;
    }

    public function requestIdentity(array $identity): array
    {
        $email = strtolower(trim($identity['email']));
        $phone = trim($identity['phone'] ?? '');
        if ($phone !== '') {
            $phone = app(PhoneNumberService::class)->normalize($phone, 'IT');
        }
        // A phone supplied by a visitor is a lookup hint, never proof of identity.
        // Phone-only matches must receive the token at the existing, undisclosed email.
        if (! Customer::where('email', $email)->exists() && $phone !== '') {
            $matches = Customer::where('phone', $phone)->limit(2)->get();
            if ($matches->count() === 1 && filter_var($matches->first()->email, FILTER_VALIDATE_EMAIL)) {
                $email = strtolower(trim($matches->first()->email));
            }
        }

        return [
            'name' => $identity['name'], 'email' => $email, 'phone' => $phone,
            'privacy_consent' => (bool) ($identity['privacy_consent'] ?? false),
            '_profile_required' => true,
        ];
    }

    public function prefill(CouponClaim $claim): array
    {
        $identity = $claim->identity;
        $customer = Customer::where('email', $identity['email'])->first();
        $visibleKeys = collect($claim->coupon->fields)->where('visible', true)->pluck('key')->all();
        $values = array_intersect_key($identity, array_flip($visibleKeys));
        if ($customer) {
            $values = array_replace($values, [
                'name' => $customer->firstname ?: $identity['name'], 'lastname' => $customer->lastname,
                'phone' => $customer->phone ?: ($identity['phone'] ?? ''),
                'birthdate' => $customer->birthdate ? \Carbon\Carbon::parse($customer->birthdate)->toDateString() : '',
                'province_id' => $customer->province_id, 'comuni_id' => $customer->comuni_id,
            ]);
            $previous = $claim->coupon->assignments()->where('customer_id', $customer->id)->first();
            foreach ($claim->coupon->fields as $field) {
                if (! in_array($field['key'], array_column(CouponService::fields(), 'key'), true)
                    && isset($previous?->profile[$field['key']])) {
                    $values[$field['key']] = $previous->profile[$field['key']];
                }
            }
        }
        $answers = [];
        foreach ($claim->coupon->fields as $field) {
            if ($field['visible']) {
                $answers[$field['key']] = $values[$field['key']] ?? ($field['type'] === 'checkbox' ? false : '');
            }
        }
        // Consent is never inferred from a stored profile or a link parameter.
        $answers['privacy_consent'] = false;
        $answers['marketing_consent'] = false;
        $answers['email'] = $identity['email'];

        return $answers;
    }

    public function validate(Coupon $coupon, array $answers, string $language, string $verifiedEmail): array
    {
        $rules = [];
        foreach ($coupon->fields as $field) {
            if (! $field['visible']) {
                continue;
            }
            $rules['answers.'.$field['key']] = array_merge([$field['required'] ? 'required' : 'nullable'], match ($field['type']) {
                'email' => ['email', 'max:254'],
                'tel' => ['string', 'max:25', new \App\Rules\ValidPhoneNumber],
                'date' => ['date', 'before_or_equal:today'],
                'time' => ['date_format:H:i'],
                'number' => ['integer', 'min:0', 'max:1000'],
                'province' => ['integer', 'exists:provinces,id'],
                'city' => ['integer', Rule::exists('comunis', 'id')->where('province_id', $answers['province_id'] ?? null)],
                'checkbox' => $field['required'] ? ['accepted'] : ['boolean'],
                'select', 'radio' => [Rule::in($field['options'][$language] ?? [])],
                default => ['string', 'max:2000'],
            });
        }
        $rules['answers.name'] = 'required|string|max:255';
        $rules['answers.email'] = ['required', 'email', Rule::in([$verifiedEmail])];
        $rules['answers.privacy_consent'] = ['accepted'];
        if (($coupon->benefit['quantity_basis'] ?? 'fixed') === 'children') {
            $rules['answers.children'] = ['required', 'integer', 'min:0', 'max:100'];
        }
        // Validate individual whitelisted keys; never persist arbitrary submitted attributes.
        $validated = Validator::make(['answers' => $answers], $rules, ['answers.email.in' => __('marketing.verified_email_locked')])->validate()['answers'];

        return array_intersect_key($validated, array_fill_keys(array_map(fn ($key) => substr($key, 8), array_keys($rules)), true));
    }
}

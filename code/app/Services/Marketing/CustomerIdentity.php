<?php

namespace App\Services\Marketing;

use App\Models\Customer;
use App\Models\MarketingPreference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerIdentity
{
    public function resolve(array $data, string $language, string $source): Customer
    {
        $email = strtolower(trim($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? '');
        if ($phone !== '') {
            $phone = app(\App\Services\PhoneNumberService::class)->normalize($phone, $data['phone_region'] ?? 'IT');
        }
        $byEmail = $email ? Customer::where('email', $email)->lockForUpdate()->first() : null;
        $byPhone = $phone ? Customer::where('phone', $phone)->lockForUpdate()->first() : null;
        if ($byEmail && $byPhone && ! $byEmail->is($byPhone) || ! $byEmail && $byPhone && $email) {
            throw ValidationException::withMessages(['identity' => __('marketing.identity_conflict')]);
        }
        $customer = $byEmail ?? $byPhone;
        $name = trim($data['name'] ?? '');
        $surname = trim($data['lastname'] ?? '');
        if (! $surname) {
            [$name,$surname] = array_pad(preg_split('/\s+/', $name, 2), 2, '');
        }
        if ($customer && ! array_key_exists('lastname', $data) && $surname === '') {
            $surname = $customer->lastname ?? '';
        }
        $values = ['firstname' => $name, 'lastname' => $surname, 'display_name' => trim($name.' '.$surname), 'lang' => $language];
        foreach (['birthdate', 'province_id', 'comuni_id'] as $field) {
            if (! empty($data[$field])) {
                $values[$field] = $data[$field];
            }
        }
        if (! $customer) {
            $customer = Customer::create($values + ['email' => $email ?: null, 'phone' => $phone ?: null, 'registration_source' => $source]);
        } else {
            $customer->update($values + ['email' => $customer->email ?: ($email ?: null), 'phone' => $customer->phone ?: ($phone ?: null)]);
        }
        if (array_key_exists('privacy_consent', $data) && $data['privacy_consent']) {
            $customer->update(['consent_privacy' => true, 'datetime_consent_privacy' => now(), 'ip_consent_privacy' => request()->ip()]);
        }
        if (array_key_exists('marketing_consent', $data)) {
            $consent = (bool) $data['marketing_consent'];
            $customer->update(['consent_marketing' => $consent, 'datetime_consent_marketing' => now(), 'ip_consent_marketing' => request()->ip()]);
            DB::table('marketing_consent_events')->insert(['customer_id' => $customer->id, 'consent' => $consent, 'source' => $source, 'policy_version' => '1', 'recorded_at' => now()]);
            if (! $consent) {
                MarketingPreference::where('customer_id', $customer->id)->update(['unsubscribed_at' => now()]);
            }
            // Explicit re-subscription requires this new affirmative form submission.
            else {
                MarketingPreference::where('customer_id', $customer->id)->update(['unsubscribed_at' => null]);
            }
        }

        return $customer;
    }

    public function preference(Customer $customer): MarketingPreference
    {
        $token = Str::random(64);

        return MarketingPreference::firstOrCreate(['customer_id' => $customer->id], ['token_hash' => hash('sha256', $token), 'access_token' => $token]);
    }
}

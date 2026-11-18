<?php

namespace App\Services\MarketingForms;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\MarketingForm;
use App\Services\BookingService;
use App\Services\Messaging\BookingMessageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicFormSubmissionService
{
    public function __construct(
        private BookingService $bookings,
        private EventScheduleService $eventSchedules,
        private BookingMessageService $messages,
    ) {}

    public function submit(MarketingForm $form, array $payload, string $language, ?\App\Models\CouponAssignment $couponAssignment = null, ?string $idempotencyKey = null): Booking|\App\Models\MarketingFormSubmission
    {
        if (! in_array($form->type, ['booking', 'event'], true)) {
            return $form->submissions()->create([
                'language' => $language,
                'field_snapshot' => $this->fieldSnapshot($form),
                'payload' => $payload,
            ]);
        }

        return DB::transaction(function () use ($form, $payload, $language, $couponAssignment, $idempotencyKey): Booking {
            MarketingForm::whereKey($form->id)->lockForUpdate()->firstOrFail();
            if ($idempotencyKey && $previous = $form->submissions()->where('idempotency_key', $idempotencyKey)->first()) {
                return $previous->booking;
            }
            if ($form->type === 'event' && ($form->schedule['mode'] ?? null) !== 'standard') {
                $this->eventSchedules->ensureAvailable($form, $payload['date'], $payload['time'], (int) $payload['guests']);
            } else {
                $this->bookings->ensureCapacity($payload['date'], $payload['time'], (int) $payload['guests']);
            }
            $customer = $couponAssignment ? $couponAssignment->customer : $this->customer($payload, $language);
            if ($couponAssignment) {
                app(\App\Services\Marketing\CouponService::class)->check($form->accepts_coupons && $couponAssignment->snapshot['marketing_form_id'] === $form->id, 'form_incompatible');
            }

            if ($couponAssignment) {
                if (! $customer->phone && ! empty($payload['phone'])) {
                    app(\App\Services\Marketing\CouponService::class)->check(! Customer::where('phone', $payload['phone'])->whereKeyNot($customer->id)->exists(), 'identity_conflict');
                    $customer->update(['phone' => $payload['phone']]);
                }
                $customer->update(['lang' => $language]);
                $this->consents($customer, $payload);
            }
            $booking = Booking::create([
                'customer_id' => $customer->id,
                'booking_date' => $payload['date'],
                'booking_time' => $payload['time'],
                'pax' => (int) $payload['guests'],
                'status' => 'pending',
                'source' => 'public-form',
                'language' => $language,
                'note' => $payload['notes'] ?? null,
            ]);

            if ($couponAssignment) {
                app(\App\Services\Marketing\CouponService::class)->consume($couponAssignment, $booking, $payload);
            }
            $deliveryId = $couponAssignment ? ($couponAssignment->snapshot['campaign_delivery_id'] ?? null) : session('marketing.delivery');
            if ($deliveryId && \App\Models\CampaignDelivery::whereKey($deliveryId)->whereHas('run', fn ($q) => $couponAssignment ? $q->where('snapshot->coupon_id', $couponAssignment->coupon_id) : $q->where('snapshot->marketing_form_id', $form->id))->exists()) {
                $booking->update(['campaign_delivery_id' => $deliveryId]);
            }
            $form->submissions()->create([
                'idempotency_key' => $idempotencyKey,
                'booking_id' => $booking->id,
                'language' => $language,
                'field_snapshot' => $this->fieldSnapshot($form),
                'payload' => $payload,
            ]);

            // Transactional outbox: booking, submission and both notification
            // intents are committed or rolled back together.
            $this->messages->customerBookingCreated($booking);

            return $booking;
        });
    }

    private function fieldSnapshot(MarketingForm $form): array
    {
        return $form->fields()->get()->map(fn ($field): array => [
            'key' => $field->key,
            'type' => $field->type,
            'label' => $field->label,
        ])->values()->all();
    }

    private function customer(array $payload, string $language): Customer
    {
        $name = trim((string) ($payload['name'] ?? ''));
        [$firstname, $lastname] = $this->splitName($name);
        $email = trim((string) ($payload['email'] ?? '')) ?: null;
        $phone = trim((string) ($payload['phone'] ?? '')) ?: null;

        $emailCustomer = $email ? Customer::query()->where('email', $email)->first() : null;
        $phoneCustomer = $phone ? Customer::query()->where('phone', $phone)->first() : null;
        if ($emailCustomer && $phoneCustomer && ! $emailCustomer->is($phoneCustomer)) {
            throw ValidationException::withMessages([
                'answers.email' => __('marketing_forms.email_phone_customer_mismatch'),
            ]);
        }
        $customer = $emailCustomer ?? $phoneCustomer;

        if (! $customer) {
            $created = Customer::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'display_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'lang' => $language,
                'registration_source' => 'public-form',
            ]);
            $this->consents($created, $payload);

            return $created;
        }

        $customer->update([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'display_name' => $name,
            'lang' => $language,
            'email' => $customer->email ?: $email,
            'phone' => $customer->phone ?: $phone,
        ]);

        $this->consents($customer, $payload);

        return $customer;
    }

    private function consents(Customer $customer, array $payload): void
    {
        if (! array_key_exists('marketing_consent', $payload) && ! array_key_exists('privacy_consent', $payload)) {
            return;
        }
        $values = [];
        if (! empty($payload['privacy_consent'])) {
            $values += ['consent_privacy' => true, 'datetime_consent_privacy' => now(), 'ip_consent_privacy' => request()->ip()];
        }
        if (array_key_exists('marketing_consent', $payload)) {
            $consent = (bool) $payload['marketing_consent'];
            $values += ['consent_marketing' => $consent, 'datetime_consent_marketing' => now(), 'ip_consent_marketing' => request()->ip()];
            DB::table('marketing_consent_events')->insert(['customer_id' => $customer->id, 'consent' => $consent, 'source' => 'public-form', 'policy_version' => '1', 'recorded_at' => now()]);
            \App\Models\MarketingPreference::where('customer_id', $customer->id)->update(['unsubscribed_at' => $consent ? null : now()]);
        }
        $customer->update($values);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [$parts[0] ?? $name, $parts[1] ?? null];
    }
}

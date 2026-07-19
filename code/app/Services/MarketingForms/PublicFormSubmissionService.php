<?php

namespace App\Services\MarketingForms;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\MarketingForm;
use App\Services\BookingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicFormSubmissionService
{
    public function __construct(private BookingService $bookings) {}

    public function submit(MarketingForm $form, array $payload, string $language): Booking|\App\Models\MarketingFormSubmission
    {
        if (! in_array($form->type, ['booking', 'event'], true)) {
            return $form->submissions()->create([
                'language' => $language,
                'field_snapshot' => $this->fieldSnapshot($form),
                'payload' => $payload,
            ]);
        }

        return DB::transaction(function () use ($form, $payload, $language): Booking {
            $this->bookings->ensureCapacity($payload['date'], $payload['time'], (int) $payload['guests']);
            $customer = $this->customer($payload, $language);

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

            $form->submissions()->create([
                'booking_id' => $booking->id,
                'language' => $language,
                'field_snapshot' => $this->fieldSnapshot($form),
                'payload' => $payload,
            ]);

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
                'answers.email' => 'Email e telefono appartengono a due clienti diversi.',
            ]);
        }
        $customer = $emailCustomer ?? $phoneCustomer;

        if (! $customer) {
            return Customer::create([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'display_name' => $name,
                'email' => $email,
                'phone' => $phone,
                'lang' => $language,
                'registration_source' => 'public-form',
            ]);
        }

        $customer->update([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'display_name' => $name,
            'lang' => $language,
            'email' => $customer->email ?: $email,
            'phone' => $customer->phone ?: $phone,
        ]);

        return $customer;
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', $name, 2) ?: [];

        return [$parts[0] ?? $name, $parts[1] ?? null];
    }
}

<?php

namespace App\Livewire\PublicBookings;

use App\Models\Booking;
use App\Models\Customer;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use App\Services\Messaging\BookingMessageService;
use App\Services\Messaging\BookingPublicUrlService;
use App\Services\TenantBrandingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PublicBookingEdit extends Component
{
    public Booking $booking;

    public Customer $customer;

    public string $language;

    public string $booking_date = '';

    public string $booking_time = '';

    public int $pax = 1;

    public string $note = '';

    public string $originalUpdatedAt = '';

    public function updatedBookingDate(): void
    {
        $this->booking_time = '';
    }

    public function mount(Customer $customer, Booking $booking, string $language): void
    {
        abort_unless($booking->customer_id === $customer->id && ! $booking->isWalkIn(), 404);
        abort_if(in_array($booking->status, ['denied', 'canceled', 'finalized', 'no-show'], true), 403);
        $this->customer = $customer;
        $this->booking = $booking;
        $this->language = $language;
        $this->booking_date = $booking->booking_date->toDateString();
        $this->booking_time = substr((string) $booking->booking_time, 0, 5);
        $this->pax = $booking->pax;
        $this->note = $booking->note ?? '';
        $this->originalUpdatedAt = $booking->updated_at->toISOString();
    }

    public function save(BookingService $bookings, BookingMessageService $messages)
    {
        $freshBooking = Booking::query()->whereKey($this->booking->id)->where('customer_id', $this->customer->id)->firstOrFail();
        if ($freshBooking->updated_at->toISOString() !== $this->originalUpdatedAt) {
            throw ValidationException::withMessages(['booking' => __('public_bookings.concurrent_update')]);
        }
        $this->booking = $freshBooking;

        $data = $this->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'pax' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);
        $bookings->ensureCapacity($data['booking_date'], $data['booking_time'], $data['pax'], $this->booking->id);

        DB::transaction(function () use ($data, $messages): void {
            $before = $this->booking->only(['booking_date', 'booking_time', 'pax', 'note', 'status']);
            Booking::withoutEvents(fn () => $this->booking->update($data + ['status' => 'pending']));
            $changes = collect($data + ['status' => 'pending'])->mapWithKeys(fn ($value, $field) => [
                $field => ['from' => $before[$field] instanceof \DateTimeInterface ? $before[$field]->format('Y-m-d') : $before[$field], 'to' => $value],
            ])->filter(fn ($change) => $change['from'] != $change['to'])->all();
            $this->booking->recordHistory('booking_edited_from_customer', __('public_bookings.history.customer_edited'), $changes, __('public_bookings.customer'));
            $messages->customerEdited($this->booking->refresh());
        });

        session()->flash('success', __('public_bookings.updated'));

        return redirect()->to(app(BookingPublicUrlService::class)->view($this->booking, $this->language));
    }

    public function render(BookingService $bookings, TenantBrandingService $branding, CustomerLanguageService $languages, BookingPublicUrlService $urls)
    {
        return view('livewire.public-bookings.edit', [
            'branding' => $branding->branding(),
            'languages' => $languages->enabled(),
            'languageUrls' => collect($languages->enabled())->mapWithKeys(fn (array $meta, string $language) => [$language => $urls->edit($this->booking, $language)])->all(),
            'viewUrl' => $urls->view($this->booking, $this->language),
            'slots' => $bookings->slots($this->booking_date),
        ])->layout('layouts.customer-booking', ['title' => __('public_bookings.edit_title')]);
    }
}

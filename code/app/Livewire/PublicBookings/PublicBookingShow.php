<?php

namespace App\Livewire\PublicBookings;

use App\Models\Booking;
use App\Models\Customer;
use App\Services\CustomerLanguageService;
use App\Services\Messaging\BookingPublicUrlService;
use App\Services\TenantBrandingService;
use Livewire\Component;

class PublicBookingShow extends Component
{
    public Booking $booking;

    public Customer $customer;

    public string $language;

    public function mount(Customer $customer, Booking $booking, string $language): void
    {
        abort_unless($booking->customer_id === $customer->id, 404);
        $this->customer = $customer;
        $this->booking = $booking->load('tables.room', 'histories');
        $this->language = $language;
    }

    public function render(
        TenantBrandingService $branding,
        CustomerLanguageService $languages,
        BookingPublicUrlService $urls,
    ) {
        return view('livewire.public-bookings.show', [
            'branding' => $branding->branding(),
            'languages' => $languages->enabled(),
            'languageUrls' => collect($languages->enabled())->mapWithKeys(
                fn (array $meta, string $language) => [$language => $urls->view($this->booking, $language)]
            )->all(),
            'editUrl' => $urls->edit($this->booking, $this->language),
            'statusLabels' => config('bookings.statuses'),
        ])->layout('layouts.customer-booking', ['title' => __('bookings.detail')]);
    }
}

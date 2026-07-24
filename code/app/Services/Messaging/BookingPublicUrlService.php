<?php

namespace App\Services\Messaging;

use App\Models\Booking;
use Illuminate\Support\Facades\URL;

class BookingPublicUrlService
{
    public function view(Booking $booking, ?string $language = null): string
    {
        return $this->signed('public.bookings.show', $booking, $language);
    }

    public function edit(Booking $booking, ?string $language = null): string
    {
        return $this->signed('public.bookings.edit', $booking, $language);
    }

    public function manage(Booking $booking): string
    {
        $this->forceAppUrl();

        return route('bookings.show', $booking);
    }

    private function signed(string $route, Booking $booking, ?string $language): string
    {
        $this->forceAppUrl();

        $relativeUrl = URL::signedRoute($route, [
            'customer' => $booking->customer_id,
            'booking' => $booking->id,
            'language' => $language ?: $booking->language ?: 'it',
        ], absolute: false);

        return rtrim((string) config('app.url'), '/').'/'.ltrim($relativeUrl, '/');
    }

    private function forceAppUrl(): void
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        URL::forceRootUrl($appUrl);
        URL::forceScheme((string) parse_url($appUrl, PHP_URL_SCHEME));
    }
}

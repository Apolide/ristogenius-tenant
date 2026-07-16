<?php

namespace App\Livewire\Customers;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Livewire\Component;

class CustomerBookingHistory extends Component
{
    public string $customerId;
    public ?string $excludeBookingId = null;

    public function mount(string $customerId, ?string $excludeBookingId = null): void
    {
        $this->customerId = $customerId;
        $this->excludeBookingId = $excludeBookingId;
    }

    public function render()
    {
        $bookings = Booking::withTrashed()
            ->where('customer_id', $this->customerId)
            ->when($this->excludeBookingId, fn ($query) => $query->where('id', '!=', $this->excludeBookingId))
            ->latest('booking_date')
            ->latest('booking_time')
            ->get();

        return view('livewire.customers.customer-booking-history', [
            'customerBookings' => $bookings,
            'statusLabels' => config('bookings.statuses'),
            'stats' => $this->stats($bookings),
        ]);
    }

    private function stats(Collection $bookings): array
    {
        return [
            'finalized' => $bookings->where('status', 'finalized')->count(),
            'canceled' => $bookings->filter(fn (Booking $booking) => $booking->trashed() || $booking->status === 'canceled')->count(),
            'denied' => $bookings->where('status', 'denied')->count(),
            'no_show' => $bookings->where('status', 'no-show')->count(),
        ];
    }
}

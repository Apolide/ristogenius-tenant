<?php

namespace App\Livewire\Bookings;

class BookingCreate extends BookingForm
{
    /**
     * The create route has no Booking model to bind. Keeping this mount method
     * parameterless prevents Livewire from interpreting "create" as a model key.
     */
    public function mount(): void
    {
        $this->booking = null;
        $this->booking_date = now()->toDateString();

        if ($customerId = request()->query('customer')) {
            $this->selectCustomer((string) $customerId);
        }
    }
}

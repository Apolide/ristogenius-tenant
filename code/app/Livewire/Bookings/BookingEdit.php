<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;

class BookingEdit extends BookingForm
{
    public function mount(Booking $booking): void
    {
        abort_if($booking->isWalkIn(), 404);
        $this->booking = $booking;
        $this->customer_id = $booking->customer_id;
        $this->firstname = $booking->customer->firstname ?: '';
        $this->lastname = $booking->customer->lastname ?: '';
        $this->email = $booking->customer->email ?: '';
        $this->phone = $booking->customer->phone ?: '';
        $this->lang = $booking->language ?: ($booking->customer->lang ?: 'it');
        $this->booking_date = $booking->booking_date->toDateString();
        $this->booking_time = substr((string) $booking->booking_time, 0, 5);
        $this->pax = $booking->pax;
        $this->status = $booking->status;
        $this->note = $booking->note ?? '';
        $this->restaurant_note = $booking->restaurant_note ?? '';
        $this->selectedTablesIds = $booking->tables()->pluck('settings_room_tables.id')->all();
        $this->tablePickerSelection = $this->selectedTablesIds;
    }
}

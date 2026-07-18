<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use Livewire\Component;

class BookingShow extends Component
{
    public Booking $booking;
    public bool $showPickTableModal = false;
    public array $tablePickerSelection = [];
    public string $searchTable = '';

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load('customer', 'tables.room', 'histories');
    }

    public function showPickTable(): void
    {
        $this->tablePickerSelection = $this->booking->tables->pluck('id')->all();
        $this->showPickTableModal = true;
    }

    public function closePickTableModal(): void
    {
        $this->showPickTableModal = false;
        $this->searchTable = '';
    }

    public function saveSelectedTables(): void
    {
        $before = $this->booking->tables->pluck('name')->all();
        $this->booking->tables()->sync($this->tablePickerSelection);
        $this->booking->load('tables.room');
        $after = $this->booking->tables->pluck('name')->all();
        $this->booking->recordHistory('tables_changed', 'Tavoli assegnati modificati', ['tables' => ['from' => $before, 'to' => $after]]);
        $this->booking->load('histories');
        $this->closePickTableModal();
        session()->flash('success', __('bookings.messages.tables_saved'));
    }

    public function render(BookingService $bookingService, CustomerLanguageService $languages)
    {
        return view('livewire.bookings.booking-show', [
            'tables' => $this->showPickTableModal
                ? $bookingService->availableTables($this->booking->booking_date->toDateString(), substr((string) $this->booking->booking_time, 0, 5), $this->booking->id, $this->searchTable)
                : collect(),
            'statusLabels' => config('bookings.statuses'),
            'bookingLanguage' => $languages->meta($this->booking->language ?: $this->booking->customer?->lang),
        ])->title(__('bookings.detail'));
    }
}

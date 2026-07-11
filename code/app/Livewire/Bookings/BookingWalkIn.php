<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class BookingWalkIn extends Component
{
    public string $booking_date = '';
    public string $booking_time = '';
    public int $pax = 1;
    public string $note = '';
    public string $lang = 'it';
    public array $selectedTablesIds = [];
    public array $tablePickerSelection = [];
    public string $searchTable = '';
    public bool $tablePickerOpen = false;

    public function mount(): void { $this->booking_date = now()->toDateString(); }
    public function updatedBookingDate(): void
    {
        $this->booking_time = '';
        $this->selectedTablesIds = [];
        $this->tablePickerSelection = [];
    }
    public function updatedBookingTime(): void
    {
        $this->selectedTablesIds = [];
        $this->tablePickerSelection = [];
    }
    public function openTablePicker(): void
    {
        if ($this->booking_time === '') {
            $this->addError('booking_time', 'Seleziona un orario prima di scegliere i tavoli.');
            return;
        }

        $this->resetErrorBag('booking_time');
        $this->tablePickerSelection = $this->selectedTablesIds;
        $this->tablePickerOpen = true;
    }
    public function confirmTables(): void
    {
        $this->selectedTablesIds = $this->tablePickerSelection;
        $this->tablePickerOpen = false;
        $this->searchTable = '';
    }
    public function closeTablePicker(): void
    {
        $this->tablePickerSelection = $this->selectedTablesIds;
        $this->tablePickerOpen = false;
        $this->searchTable = '';
    }

    public function save(BookingService $service)
    {
        $languages = array_keys(app(CustomerLanguageService::class)->enabled());
        $data = $this->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'], 'booking_time' => ['required', 'date_format:H:i'],
            'pax' => ['required', 'integer', 'min:1'], 'note' => ['nullable', 'string', 'max:5000'],
            'lang' => ['required', Rule::in($languages)],
            'selectedTablesIds' => ['array'], 'selectedTablesIds.*' => ['uuid', 'exists:settings_room_tables,id'],
        ]);
        $service->ensureCapacity($data['booking_date'], $data['booking_time'], $data['pax']);
        $booking = Booking::create(collect($data)->only(['booking_date', 'booking_time', 'pax', 'note'])->all() + [
            'customer_id' => null, 'status' => 'seated', 'source' => 'walk-in', 'language' => $data['lang'], 'seated_at' => now(),
        ]);
        $booking->tables()->sync($this->selectedTablesIds);
        session()->flash('success', 'Walk In inserito correttamente.');
        return redirect()->route('bookings.index', ['date' => $booking->booking_date->toDateString()]);
    }

    public function render(BookingService $service, CustomerLanguageService $languages)
    {
        return view('livewire.bookings.booking-walk-in', [
            'slots' => $service->slots($this->booking_date),
            'tables' => $this->tablePickerOpen ? $service->availableTables($this->booking_date, $this->booking_time, null, $this->searchTable) : collect(),
            'timeslotStats' => $service->timeslotStats($this->booking_date),
            'languages' => $languages->enabled(),
        ])->title('Nuovo Walk In');
    }
}

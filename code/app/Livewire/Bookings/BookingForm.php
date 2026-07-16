<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\Customer;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use App\Services\PhoneCountryService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class BookingForm extends Component
{
    public ?Booking $booking = null;
    public string $customer_id = '';
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $phone = '';
    public string $phone_prefix = '+39';
    public string $lang = 'it';
    public array $customerSuggestions = [];
    public ?string $activeSearchField = null;
    public string $booking_date = '';
    public string $booking_time = '';
    public int $pax = 1;
    public string $status = 'accepted';
    public string $note = '';
    public array $selectedTablesIds = [];
    public array $tablePickerSelection = [];
    public bool $tablePickerOpen = false;
    public string $searchTable = '';

    public function updatedBookingDate(): void { $this->booking_time = ''; }
    public function updatedPhone(): void { $this->searchCustomers('phone'); }
    public function updatedEmail(): void { $this->searchCustomers('email'); }
    public function updatedFirstname(): void { $this->searchCustomers('firstname'); }
    public function updatedLastname(): void { $this->searchCustomers('lastname'); }

    public function openTablePicker(): void
    {
        if ($this->booking_date === '' || $this->booking_time === '') {
            $this->addError('booking_time', 'Seleziona data e orario prima di assegnare i tavoli.');
            return;
        }
        $this->tablePickerSelection = $this->selectedTablesIds;
        $this->tablePickerOpen = true;
    }

    public function confirmTables(): void
    {
        $this->selectedTablesIds = $this->tablePickerSelection;
        $this->tablePickerOpen = false;
    }

    public function closeTablePicker(): void
    {
        $this->tablePickerSelection = $this->selectedTablesIds;
        $this->tablePickerOpen = false;
        $this->searchTable = '';
    }

    public function selectCustomer(string $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->customer_id = $customer->id;
        $this->firstname = $customer->firstname ?: '';
        $this->lastname = $customer->lastname ?: '';
        $this->email = $customer->email ?: '';
        $this->phone = $customer->phone ?: '';
        $this->lang = $customer->lang ?: 'it';
        $this->customerSuggestions = [];
        $this->activeSearchField = null;
    }

    public function dismissSuggestions(): void
    {
        $this->customerSuggestions = [];
        $this->activeSearchField = null;
    }

    public function save(BookingService $service)
    {
        $languages = array_keys(app(CustomerLanguageService::class)->enabled());
        $data = $this->validate([
            'firstname' => ['required', 'string', 'max:100'], 'lastname' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'], 'phone' => ['required', 'string', 'max:25'],
            'phone_prefix' => ['required', 'regex:/^\+[0-9]{1,4}$/'], 'lang' => ['required', Rule::in($languages)],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'], 'pax' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(array_keys(config('bookings.statuses')))], 'note' => ['nullable', 'string', 'max:5000'],
            'selectedTablesIds' => ['array'], 'selectedTablesIds.*' => ['uuid', 'exists:settings_room_tables,id'],
        ]);
        $service->ensureCapacity($data['booking_date'], $data['booking_time'], $data['pax'], $this->booking?->id);

        $customerData = [
            'firstname' => $data['firstname'], 'lastname' => $data['lastname'],
            'display_name' => trim($data['firstname'].' '.$data['lastname']), 'email' => $data['email'] ?: null,
            'phone' => str_starts_with($data['phone'], '+') ? $data['phone'] : $data['phone_prefix'].preg_replace('/\D+/', '', $data['phone']),
            'lang' => $data['lang'], 'registration_source' => 'backoffice',
        ];
        $customer = $this->customer_id !== '' ? Customer::findOrFail($this->customer_id) : Customer::create($customerData);
        $customer->update($customerData);

        $bookingData = collect($data)->only(['booking_date', 'booking_time', 'pax', 'status', 'note'])->all()
            + ['customer_id' => $customer->id, 'source' => 'backoffice', 'language' => $data['lang']];
        $previousTables = $this->booking?->tables()->pluck('settings_room_tables.name')->all() ?? [];
        if ($this->booking) $this->booking->update($bookingData); else $this->booking = Booking::create($bookingData);
        $this->booking->tables()->sync($this->selectedTablesIds);
        $currentTables = $this->booking->tables()->pluck('settings_room_tables.name')->all();
        if ($previousTables !== $currentTables) {
            $this->booking->recordHistory('tables_changed', 'Tavoli assegnati modificati', ['tables' => ['from' => $previousTables, 'to' => $currentTables]]);
        }
        session()->flash('success', 'Prenotazione salvata correttamente.');
        return redirect()->route('bookings.show', $this->booking);
    }

    public function delete()
    {
        abort_unless($this->booking, 404); $this->booking->delete();
        session()->flash('success', 'Prenotazione eliminata.');
        return redirect()->route('bookings.index');
    }

    public function render(BookingService $service, CustomerLanguageService $languages, PhoneCountryService $countries)
    {
        return view('livewire.bookings.booking-form', [
            'slots' => $service->slots($this->booking_date), 'statuses' => config('bookings.statuses'),
            'languages' => $languages->enabled(), 'countries' => $countries->countries(),
            'tables' => $this->tablePickerOpen
                ? $service->availableTables($this->booking_date, $this->booking_time, $this->booking?->id, $this->searchTable)
                : collect(),
        ])->title($this->booking ? 'Modifica prenotazione' : 'Nuova prenotazione');
    }

    private function searchCustomers(string $field): void
    {
        // A newly typed value detaches a previously selected customer until another suggestion is chosen.
        $this->customer_id = '';
        $this->activeSearchField = $field;
        $this->customerSuggestions = app(BookingService::class)->searchCustomers($field, $this->{$field});
    }
}

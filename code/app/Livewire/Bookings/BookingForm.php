<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\Customer;
use App\Rules\ValidPhoneNumber;
use App\Services\BookingService;
use App\Services\CustomerLanguageService;
use App\Services\Messaging\BookingMessageService;
use App\Services\PhoneCountryService;
use App\Services\PhoneNumberService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    public string $phone_region = 'IT';

    public string $lang = 'it';

    public array $customerSuggestions = [];

    public ?string $activeSearchField = null;

    public string $booking_date = '';

    public string $booking_time = '';

    public int $pax = 1;

    public string $status = 'accepted';

    public string $note = '';

    public bool $send_booking_proposal = false;

    public string $restaurant_note = '';

    public array $selectedTablesIds = [];

    public array $tablePickerSelection = [];

    public bool $tablePickerOpen = false;

    public string $searchTable = '';

    public function updatedBookingDate(): void
    {
        $this->booking_time = '';
    }

    public function updatedPhone(): void
    {
        $this->searchCustomers('phone');
    }

    public function updatedEmail(): void
    {
        $this->searchCustomers('email');
    }

    public function updatedFirstname(): void
    {
        $this->searchCustomers('firstname');
    }

    public function updatedLastname(): void
    {
        $this->searchCustomers('lastname');
    }

    public function openTablePicker(): void
    {
        if ($this->booking_date === '' || $this->booking_time === '') {
            $this->addError('booking_time', __('bookings.messages.select_datetime'));

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

    public function save(BookingService $service, BookingMessageService $messages, PhoneNumberService $phoneNumbers)
    {
        // A booking created by authenticated backoffice personnel is already
        // accepted. Ignore any client-side attempt to submit another status.
        if ($this->booking === null) {
            $this->status = 'accepted';
        } else {
            $this->restoreImmutableEditFields();
        }

        $languages = array_keys(app(CustomerLanguageService::class)->enabled());
        $data = $this->validate([
            'firstname' => ['required', 'string', 'max:100'], 'lastname' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:25', 'required_without:email', new ValidPhoneNumber($this->phone_region)],
            'phone_region' => ['nullable', 'required_with:phone', Rule::in(array_column(app(PhoneCountryService::class)->countries(), 'region'))], 'lang' => ['required', Rule::in($languages)],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'booking_time' => ['required', 'date_format:H:i'], 'pax' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(array_keys(config('bookings.statuses')))], 'note' => ['nullable', 'string', 'max:5000'],
            'send_booking_proposal' => ['boolean'],
            'restaurant_note' => ['nullable', 'required_if:send_booking_proposal,true', 'string', 'max:5000'],
            'selectedTablesIds' => ['array'], 'selectedTablesIds.*' => ['uuid', 'exists:settings_room_tables,id'],
        ]);
        $isCreating = $this->booking === null;
        $originalStatus = $this->booking?->status;
        if (! $isCreating && $data['send_booking_proposal'] && ! $this->hasProposalChanges($data)) {
            throw ValidationException::withMessages([
                'send_booking_proposal' => __('bookings.proposal_requires_changes'),
            ]);
        }
        $service->ensureCapacity($data['booking_date'], $data['booking_time'], $data['pax'], $this->booking?->id);
        $email = $data['email'] ?: null;
        $phone = $phoneNumbers->normalize($data['phone'] ?? null, $data['phone_region'] ?? 'IT');
        $this->validateUniqueContacts($email, $phone);

        try {
            DB::transaction(function () use ($data, $email, $phone, $messages, $isCreating, $originalStatus): void {
                $customerData = [
                    'firstname' => $data['firstname'], 'lastname' => $data['lastname'] ?: null,
                    'display_name' => trim($data['firstname'].' '.($data['lastname'] ?? '')), 'email' => $email,
                    'phone' => $phone, 'lang' => $data['lang'], 'registration_source' => 'backoffice',
                ];
                $customer = $this->customer_id !== '' ? Customer::findOrFail($this->customer_id) : Customer::create($customerData);
                if ($isCreating) {
                    $customer->update($customerData);
                }

                $bookingData = collect($data)->only(['booking_date', 'booking_time', 'pax', 'status', 'note', 'restaurant_note'])->all()
                    + ['customer_id' => $customer->id, 'source' => 'backoffice', 'language' => $data['lang']];
                $previousTables = $this->booking?->tables()->pluck('settings_room_tables.name')->all() ?? [];
                if ($this->booking) {
                    $this->booking->update($bookingData);
                } else {
                    $this->booking = Booking::create($bookingData);
                }
                $this->booking->tables()->sync($this->selectedTablesIds);
                $currentTables = $this->booking->tables()->pluck('settings_room_tables.name')->all();
                if ($previousTables !== $currentTables) {
                    $this->booking->recordHistory('tables_changed', 'Tavoli assegnati modificati', ['tables' => ['from' => $previousTables, 'to' => $currentTables]]);
                }
                if ($isCreating) {
                    $messages->bookingCreated($this->booking);
                } elseif ($data['send_booking_proposal']) {
                    $this->booking->recordHistory(
                        'booking_proposal',
                        $data['restaurant_note'],
                    );
                    $messages->bookingProposal($this->booking->refresh());
                } elseif ($originalStatus !== $this->booking->status) {
                    $messages->bookingStatusChanged($this->booking->refresh());
                }
            });
        } catch (UniqueConstraintViolationException $exception) {
            // Covers the race between the pre-check and the database insert.
            $this->validateUniqueContacts($email, $phone, $exception);

            throw $exception;
        }
        session()->flash('success', __('bookings.messages.saved'));

        return redirect()->route('bookings.show', $this->booking);
    }

    public function delete()
    {
        abort_unless($this->booking, 404);
        $this->booking->delete();
        session()->flash('success', __('bookings.messages.deleted'));

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
        ])->title($this->booking ? __('bookings.edit') : __('bookings.new'));
    }

    private function searchCustomers(string $field): void
    {
        // A newly typed value detaches a previously selected customer until another suggestion is chosen.
        $this->customer_id = '';
        $this->activeSearchField = $field;
        $this->customerSuggestions = app(BookingService::class)->searchCustomers($field, $this->{$field});
    }

    private function hasProposalChanges(array $data): bool
    {
        if (! $this->booking) {
            return false;
        }

        return $this->booking->booking_date->toDateString() !== $data['booking_date']
            || substr((string) $this->booking->booking_time, 0, 5) !== $data['booking_time']
            || $this->booking->pax !== $data['pax'];
    }

    private function restoreImmutableEditFields(): void
    {
        $booking = $this->booking->fresh('customer');
        $this->booking = $booking;
        $this->customer_id = $booking->customer_id;
        $this->firstname = $booking->customer->firstname ?: '';
        $this->lastname = $booking->customer->lastname ?: '';
        $this->email = $booking->customer->email ?: '';
        $this->phone = $booking->customer->phone ?: '';
        $this->lang = $booking->language ?: ($booking->customer->lang ?: 'it');
        $this->status = $booking->status;
    }

    private function validateUniqueContacts(
        ?string $email,
        ?string $phone,
        ?UniqueConstraintViolationException $exception = null,
    ): void {
        foreach (['email' => $email, 'phone' => $phone] as $field => $value) {
            if (! $value) {
                continue;
            }

            $duplicate = Customer::query()
                ->where($field, $value)
                ->when($this->customer_id !== '', fn ($query) => $query->whereKeyNot($this->customer_id))
                ->first(['id']);

            if (! $duplicate) {
                continue;
            }

            try {
                Log::warning('Creazione booking bloccata per recapito cliente duplicato', [
                    'field' => $field,
                    'contact_hash' => hash('sha256', $value),
                    'existing_customer_id' => $duplicate->id,
                    'selected_customer_id' => $this->customer_id ?: null,
                    'booking_id' => $this->booking?->id,
                    'user_id' => auth()->id(),
                    'database_exception' => $exception !== null,
                ]);
            } catch (\Throwable) {
                // A logging filesystem issue must not replace the validation error shown in the UI.
            }

            throw ValidationException::withMessages([
                $field => __("bookings.messages.customer_{$field}_taken"),
            ]);
        }
    }
}

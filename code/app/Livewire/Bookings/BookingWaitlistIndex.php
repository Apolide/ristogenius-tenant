<?php

namespace App\Livewire\Bookings;

use App\Models\Booking;
use App\Models\BookingWaitlistEntry;
use App\Models\Customer;
use App\Services\BookingService;
use App\Services\WaitlistNotificationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BookingWaitlistIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showForm = true;
    public string $firstname = '';
    public string $lastname = '';
    public string $email = '';
    public string $phone = '';
    public string $customer_id = '';
    public array $customerSuggestions = [];
    public ?string $activeSearchField = null;
    public int $pax = 1;
    public string $note = '';

    public function updatedSearch(): void
    {
        $this->customer_id = '';
        $this->activeSearchField = 'search';
        $this->customerSuggestions = app(BookingService::class)->searchCustomersByTerm($this->search);
    }

    public function toggleForm(): void
    {
        $this->showForm = ! $this->showForm;
    }

    public function selectCustomer(string $id): void
    {
        $customer = Customer::findOrFail($id);
        $this->customer_id = $customer->id;
        $this->firstname = $customer->firstname ?: '';
        $this->lastname = $customer->lastname ?: '';
        $this->email = $customer->email ?: '';
        $this->phone = $customer->phone ?: '';
        $this->search = $customer->display_name ?: trim($customer->firstname.' '.$customer->lastname);
        $this->showForm = true;
        $this->customerSuggestions = [];
        $this->activeSearchField = null;
    }

    public function dismissSuggestions(): void
    {
        $this->customerSuggestions = [];
        $this->activeSearchField = null;
    }

    public function createWaitlistBooking(): void
    {
        $data = $this->validate([
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:25', 'required_without:email'],
            'pax' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:5000'],
        ], [
            'email.required_without' => 'Inserisci almeno un recapito email o telefono.',
            'phone.required_without' => 'Inserisci almeno un recapito email o telefono.',
        ]);

        DB::transaction(function () use ($data): void {
            $customerData = [
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'display_name' => trim($data['firstname'].' '.$data['lastname']),
                'email' => $data['email'] ?: null,
                'phone' => $data['phone'] ?: null,
                'lang' => 'it',
                'registration_source' => 'waitlist',
            ];
            $customer = $this->customer_id !== '' ? Customer::findOrFail($this->customer_id) : Customer::create($customerData);
            $customer->update($customerData);
            BookingWaitlistEntry::create([
                'customer_id' => $customer->id,
                'waitlist_date' => today(),
                'pax' => $data['pax'],
                'note' => $data['note'] ?: null,
            ]);
        });

        $this->reset(['search', 'firstname', 'lastname', 'email', 'phone', 'note', 'customer_id', 'customerSuggestions', 'activeSearchField']);
        $this->pax = 1;
        session()->flash('success', 'Cliente aggiunto alla lista d’attesa.');
    }

    public function notify(string $id, WaitlistNotificationService $notifications): void
    {
        $entry = $this->waitingEntry($id);
        $sent = $notifications->send($entry);

        if ($sent === []) {
            $this->addError('notification', 'Nessun canale disponibile: verifica i recapiti del cliente e la configurazione messaggi.');
            return;
        }

        $entry->update(['status' => 'notified', 'notified_at' => now(), 'notified_channels' => $sent]);
        session()->flash('success', 'Cliente avvisato via '.implode(', ', $sent).'.');
    }

    public function seat(string $id): void
    {
        $entry = BookingWaitlistEntry::with('customer')->whereDate('waitlist_date', today())
            ->whereIn('status', ['waiting', 'notified'])->findOrFail($id);

        DB::transaction(function () use ($entry): void {
            Booking::create([
                'customer_id' => $entry->customer_id,
                'booking_date' => today(),
                'booking_time' => now()->format('H:i'),
                'pax' => $entry->pax,
                'status' => 'seated',
                'source' => 'waitlist',
                'language' => $entry->customer->lang ?: 'it',
                'note' => $entry->note,
                'seated_at' => now(),
            ]);
            $entry->update(['status' => 'seated', 'seated_at' => now()]);
        });

        session()->flash('success', 'Cliente accomodato e aggiunto alle prenotazioni di oggi.');
    }

    public function cancel(string $id): void
    {
        $this->waitingEntry($id)->update(['status' => 'cancelled']);
        session()->flash('success', 'Cliente rimosso dalla lista d’attesa.');
    }

    public function render()
    {
        $entries = BookingWaitlistEntry::query()->with('customer')
            ->whereDate('waitlist_date', today())
            ->whereIn('status', ['waiting', 'notified'])
            ->oldest()->paginate(15);

        return view('livewire.bookings.booking-waitlist-index', compact('entries'))->title('Lista d’attesa');
    }

    private function waitingEntry(string $id): BookingWaitlistEntry
    {
        return BookingWaitlistEntry::with('customer')->whereDate('waitlist_date', today())
            ->whereIn('status', ['waiting', 'notified'])->findOrFail($id);
    }

}

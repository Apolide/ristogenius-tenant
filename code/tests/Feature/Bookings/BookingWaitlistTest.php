<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingWaitlistIndex;
use App\Mail\WaitlistTableReadyMail;
use App\Models\Booking;
use App\Models\BookingWaitlistEntry;
use App\Models\Customer;
use App\Models\TenantProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class BookingWaitlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_a_customer_to_todays_waitlist(): void
    {
        Livewire::test(BookingWaitlistIndex::class)
            ->set('firstname', 'Mario')->set('lastname', 'Rossi')
            ->set('email', 'mario@example.com')->set('pax', 3)
            ->call('createWaitlistBooking')->assertHasNoErrors();

        $entry = BookingWaitlistEntry::with('customer')->firstOrFail();
        $this->assertSame('Mario Rossi', $entry->customer->display_name);
        $this->assertSame(3, $entry->pax);
        $this->assertSame('waiting', $entry->status);
        $this->assertTrue($entry->waitlist_date->isToday());
    }

    public function test_it_requires_at_least_one_contact_method(): void
    {
        Livewire::test(BookingWaitlistIndex::class)
            ->set('firstname', 'Mario')->set('pax', 2)
            ->call('createWaitlistBooking')
            ->assertHasErrors(['email', 'phone']);

        $this->assertSame(0, BookingWaitlistEntry::count());
    }

    public function test_it_notifies_by_an_available_configured_channel(): void
    {
        Mail::fake();
        TenantProfile::create([
            'name' => 'Ristorante Test',
            'settings' => ['messages' => ['message_channel_cases' => [
                'waitlist_table_ready' => ['channels' => ['email']],
            ]]],
        ]);
        $entry = $this->entry();

        Livewire::test(BookingWaitlistIndex::class)->call('notify', $entry->id)->assertHasNoErrors();

        Mail::assertSent(WaitlistTableReadyMail::class, fn ($mail) => $mail->hasTo('mario@example.com'));
        $this->assertSame('notified', $entry->fresh()->status);
        $this->assertSame(['email'], $entry->fresh()->notified_channels);
    }

    public function test_it_converts_a_waitlist_entry_to_a_seated_booking(): void
    {
        $entry = $this->entry();

        Livewire::test(BookingWaitlistIndex::class)->call('seat', $entry->id);

        $booking = Booking::firstOrFail();
        $this->assertSame($entry->customer_id, $booking->customer_id);
        $this->assertSame('waitlist', $booking->source);
        $this->assertSame('seated', $booking->status);
        $this->assertSame('seated', $entry->fresh()->status);
    }

    private function entry(): BookingWaitlistEntry
    {
        $customer = Customer::create([
            'firstname' => 'Mario', 'lastname' => 'Rossi', 'display_name' => 'Mario Rossi',
            'email' => 'mario@example.com', 'lang' => 'it', 'registration_source' => 'waitlist',
        ]);

        return BookingWaitlistEntry::create([
            'customer_id' => $customer->id, 'waitlist_date' => today(), 'pax' => 2,
        ]);
    }
}

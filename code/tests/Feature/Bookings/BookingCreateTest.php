<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingCreate;
use App\Livewire\Bookings\BookingEdit;
use App\Livewire\Bookings\BookingShow;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\MessageOutbox;
use App\Models\TenantProfile;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class BookingCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('tenant.customer_languages', 'it,en');
        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('slots')->andReturn(['20:00' => ['label' => '20:00', 'meal' => 'cena']]);
        $service->shouldReceive('ensureCapacity')->andReturnNull();
        $service->shouldReceive('searchCustomers')->andReturn([])->byDefault();
        $this->app->instance(BookingService::class, $service);
    }

    public function test_it_creates_a_customer_and_booking_from_the_form(): void
    {
        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('lastname', 'Bianchi')
            ->set('email', 'giulia@example.test')->set('phone_prefix', '+39')->set('phone', '333 123 4567')
            ->set('lang', 'it')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 4)->set('status', 'pending')->set('note', 'Seggiolone')
            ->call('save')->assertHasNoErrors();

        $customer = Customer::where('email', 'giulia@example.test')->firstOrFail();
        $this->assertSame('+393331234567', $customer->phone);
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id, 'pax' => 4, 'status' => 'accepted', 'source' => 'backoffice', 'language' => 'it',
        ]);
        $this->assertDatabaseHas('message_outboxes', [
            'aggregate_id' => Booking::firstOrFail()->id,
            'status' => MessageOutbox::STATUS_PENDING,
        ]);
    }

    public function test_it_accepts_a_phone_without_an_email_and_does_not_send_email(): void
    {
        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('lastname', 'Bianchi')
            ->set('email', '')->set('phone_prefix', '+39')->set('phone', '333 123 4567')
            ->set('lang', 'it')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('customers', ['email' => null, 'phone' => '+393331234567']);
        $this->assertSame('booking_accepted', MessageOutbox::firstOrFail()->payload['message_case']);
    }

    public function test_it_accepts_an_email_without_a_phone(): void
    {
        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('lastname', 'Bianchi')
            ->set('email', 'giulia@example.test')->set('phone', '')->set('phone_prefix', '')
            ->set('lang', 'it')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('customers', ['email' => 'giulia@example.test', 'phone' => null]);
        $this->assertSame('booking_accepted', MessageOutbox::firstOrFail()->payload['message_case']);
    }

    public function test_it_accepts_a_booking_without_a_customer_lastname(): void
    {
        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('lastname', '')
            ->set('email', 'giulia@example.test')->set('phone', '')
            ->set('lang', 'it')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('customers', [
            'firstname' => 'Giulia',
            'lastname' => null,
            'display_name' => 'Giulia',
        ]);
    }

    public function test_it_requires_at_least_email_or_phone(): void
    {
        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('lastname', 'Bianchi')
            ->set('email', '')->set('phone', '')
            ->set('lang', 'it')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')->assertHasErrors(['email', 'phone']);

        $this->assertSame(0, Booking::count());
    }

    public function test_it_shows_and_logs_a_duplicate_phone_error(): void
    {
        Log::spy();
        Customer::create([
            'firstname' => 'Mario', 'display_name' => 'Mario',
            'email' => null, 'phone' => '+393331112222', 'lang' => 'it',
            'registration_source' => 'backoffice',
        ]);

        Livewire::test(BookingCreate::class)
            ->set('firstname', 'AAA')->set('lastname', '')
            ->set('email', '')->set('phone_region', 'IT')->set('phone', '333 111 2222')
            ->set('lang', 'en')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')->assertHasErrors(['phone']);

        $this->assertSame(1, Customer::count());
        $this->assertSame(0, Booking::count());
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $context['field'] === 'phone'
                && $context['contact_hash'] === hash('sha256', '+393331112222'));
    }

    public function test_it_rejects_letters_and_numbers_invalid_for_the_selected_country(): void
    {
        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('email', '')
            ->set('phone_region', 'IT')->set('phone', 'numero abc')
            ->set('lang', 'it')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')
            ->assertHasErrors(['phone']);

        $this->assertSame(0, Booking::count());
    }

    public function test_it_records_the_outbox_without_sending_synchronously(): void
    {
        TenantProfile::create([
            'name' => 'Ristopilot',
            'settings' => ['messages' => ['message_channel_cases' => [
                'booking_accepted' => ['channels' => ['whatsapp']],
            ]]],
        ]);

        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('lastname', 'Bianchi')
            ->set('email', 'giulia@example.test')->set('phone', '')
            ->set('lang', 'it')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')->assertHasNoErrors();

        $this->assertSame(1, MessageOutbox::count());
        $this->assertSame('booking_accepted', MessageOutbox::firstOrFail()->payload['message_case']);
    }

    public function test_accepting_a_pending_booking_marks_it_as_sent_and_queues_booking_sent(): void
    {
        $booking = $this->existingBooking('pending');

        Livewire::test(BookingShow::class, ['booking' => $booking])
            ->call('accept')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'booking_sent']);
        $this->assertSame('booking_sent', MessageOutbox::firstOrFail()->payload['message_case']);
    }

    public function test_edit_page_cannot_change_customer_or_booking_status(): void
    {
        $booking = $this->existingBooking('booking_sent');

        Livewire::test(BookingEdit::class, ['booking' => $booking])
            ->assertSee(__('bookings.form.send_proposal'))
            ->assertDontSee(__('bookings.form.hint'))
            ->assertDontSeeHtml('wire:model="status"')
            ->assertDontSeeHtml('wire:model.live.debounce.800ms="firstname"')
            ->set('firstname', 'Nome manipolato')
            ->set('email', 'changed@example.test')
            ->set('lang', 'en')
            ->set('status', 'denied')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'booking_sent', 'language' => 'it']);
        $this->assertDatabaseHas('customers', ['id' => $booking->customer_id, 'firstname' => 'Giulia', 'email' => 'giulia@example.test']);
        $this->assertSame(0, MessageOutbox::count());
    }

    public function test_booking_proposal_requires_a_change_and_includes_restaurant_notes(): void
    {
        $booking = $this->existingBooking('booking_sent');
        $component = Livewire::test(BookingEdit::class, ['booking' => $booking])
            ->set('send_booking_proposal', true)
            ->set('restaurant_note', 'Possiamo ospitarvi mezz’ora più tardi.')
            ->call('save')
            ->assertHasErrors(['send_booking_proposal']);

        $this->assertSame(0, MessageOutbox::count());

        $component
            ->set('booking_time', '20:30')
            ->call('save')
            ->assertHasNoErrors();

        $outbox = MessageOutbox::firstOrFail();
        $this->assertSame('booking_proposal', $outbox->payload['message_case']);
        $this->assertStringContainsString('Possiamo ospitarvi mezz’ora più tardi.', $outbox->payload['deliveries'][0]['content']['additional_note']);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'restaurant_note' => 'Possiamo ospitarvi mezz’ora più tardi.',
        ]);
    }

    public function test_selecting_a_suggestion_populates_existing_customer_data(): void
    {
        $customer = Customer::create([
            'firstname' => 'Mario', 'lastname' => 'Rossi', 'display_name' => 'Mario Rossi',
            'email' => 'mario@example.test', 'phone' => '+393331234567', 'lang' => 'en',
            'registration_source' => 'backoffice',
        ]);

        Livewire::test(BookingCreate::class)->call('selectCustomer', $customer->id)
            ->assertSet('customer_id', $customer->id)->assertSet('firstname', 'Mario')
            ->assertSet('email', 'mario@example.test')->assertSet('lang', 'en');
    }

    public function test_it_rejects_an_unsupported_customer_language(): void
    {
        Livewire::test(BookingCreate::class)
            ->set('firstname', 'Giulia')->set('lastname', 'Bianchi')->set('phone', '3331234567')
            ->set('lang', 'de')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)->call('save')
            ->assertHasErrors(['lang']);

        $this->assertSame(0, Booking::count());
    }

    private function existingBooking(string $status): Booking
    {
        $customer = Customer::create([
            'firstname' => 'Giulia', 'display_name' => 'Giulia', 'email' => 'giulia@example.test',
            'registration_source' => 'backoffice', 'lang' => 'it',
        ]);

        return Booking::create([
            'customer_id' => $customer->id,
            'booking_date' => now()->addDay(),
            'booking_time' => '20:00',
            'pax' => 2,
            'status' => $status,
            'source' => 'public-form',
            'language' => 'it',
        ]);
    }
}

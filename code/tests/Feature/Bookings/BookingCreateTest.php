<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingCreate;
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
            ->set('booking_time', '20:00')->set('pax', 4)->set('note', 'Seggiolone')
            ->call('save')->assertHasNoErrors();

        $customer = Customer::where('email', 'giulia@example.test')->firstOrFail();
        $this->assertSame('+393331234567', $customer->phone);
        $this->assertDatabaseHas('bookings', [
            'customer_id' => $customer->id, 'pax' => 4, 'source' => 'backoffice', 'language' => 'it',
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
            'email' => null, 'phone' => '+39060606', 'lang' => 'it',
            'registration_source' => 'backoffice',
        ]);

        Livewire::test(BookingCreate::class)
            ->set('firstname', 'AAA')->set('lastname', '')
            ->set('email', '')->set('phone_prefix', '+39')->set('phone', '060606')
            ->set('lang', 'en')->set('booking_date', now()->addDay()->toDateString())
            ->set('booking_time', '20:00')->set('pax', 2)
            ->call('save')->assertHasErrors(['phone']);

        $this->assertSame(1, Customer::count());
        $this->assertSame(0, Booking::count());
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(fn (string $message, array $context): bool => $context['field'] === 'phone'
                && $context['contact_hash'] === hash('sha256', '+39060606'));
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
}

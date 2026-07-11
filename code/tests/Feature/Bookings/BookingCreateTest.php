<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingCreate;
use App\Models\Booking;
use App\Models\Customer;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

<?php

namespace Tests\Feature\Bookings;

use App\Livewire\PublicBookings\PublicBookingEdit;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\MessageOutbox;
use App\Services\BookingService;
use App\Services\Messaging\BookingPublicUrlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_open_the_signed_booking_page(): void
    {
        [$customer, $booking] = $this->booking();

        $this->get(app(BookingPublicUrlService::class)->view($booking, 'it'))
            ->assertOk()
            ->assertSee('La tua prenotazione');
    }

    public function test_unsigned_booking_page_is_rejected(): void
    {
        [$customer, $booking] = $this->booking();

        $this->get(route('public.bookings.show', [$customer, $booking, 'it']))->assertForbidden();
    }

    public function test_customer_edit_sets_pending_records_history_and_outbox(): void
    {
        [$customer, $booking] = $this->booking();
        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('slots')->andReturn(['20:30' => ['label' => '20:30', 'meal' => 'cena']]);
        $service->shouldReceive('ensureCapacity')->once()->andReturnNull();
        $this->app->instance(BookingService::class, $service);

        Livewire::test(PublicBookingEdit::class, compact('customer', 'booking') + ['language' => 'it'])
            ->set('booking_time', '20:30')
            ->set('pax', 4)
            ->set('note', 'Tavolo tranquillo')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'pending', 'pax' => 4]);
        $this->assertDatabaseHas('booking_histories', ['booking_id' => $booking->id, 'event' => 'booking_edited_from_customer']);
        $this->assertSame('booking_edited_from_customer', MessageOutbox::query()->latest()->firstOrFail()->payload['message_case']);
    }

    private function booking(): array
    {
        config()->set('tenant.customer_languages', 'it,en');
        $customer = Customer::create([
            'firstname' => 'Giulia', 'display_name' => 'Giulia', 'email' => 'giulia@example.test',
            'registration_source' => 'backoffice', 'lang' => 'it',
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id, 'booking_date' => now()->addDay(), 'booking_time' => '20:00',
            'pax' => 2, 'status' => 'accepted', 'source' => 'backoffice', 'language' => 'it',
        ]);

        return [$customer, $booking];
    }
}

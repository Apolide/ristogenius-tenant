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

        $urls = app(BookingPublicUrlService::class);

        $this->get($urls->view($booking, 'it'))
            ->assertOk()
            ->assertSee('La tua prenotazione')
            ->assertSee('id="booking-language"', false)
            ->assertSee('selected', false)
            ->assertSee($urls->view($booking, 'it'))
            ->assertSee($urls->view($booking, 'en'));
    }

    public function test_customer_signed_edit_page_language_select_contains_signed_urls(): void
    {
        [$customer, $booking] = $this->booking();
        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('availableSlots')->once()->andReturn([]);
        $this->app->instance(BookingService::class, $service);
        $urls = app(BookingPublicUrlService::class);

        $this->get($urls->edit($booking, 'it'))
            ->assertOk()
            ->assertSee('id="booking-language"', false)
            ->assertSee('selected', false)
            ->assertSee('this.showPicker', false)
            ->assertSee('dark:[color-scheme:dark]', false)
            ->assertSee($urls->edit($booking, 'it'))
            ->assertSee($urls->edit($booking, 'en'));
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
        $service->shouldReceive('availableSlots')->andReturn(['20:30' => ['label' => '20:30', 'meal' => 'cena']]);
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

    public function test_customer_can_cancel_booking_and_staff_notification_is_queued(): void
    {
        [$customer, $booking] = $this->booking();

        Livewire::test(PublicBookingEdit::class, compact('customer', 'booking') + ['language' => 'it'])
            ->call('cancelBooking')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'canceled']);
        $this->assertDatabaseHas('booking_histories', [
            'booking_id' => $booking->id,
            'event' => 'booking_canceled',
            'actor' => 'Cliente',
        ]);
        $outbox = MessageOutbox::query()->latest()->firstOrFail();
        $this->assertSame('booking_canceled_from_customer', $outbox->payload['message_case']);
        $this->assertSame('staff', $outbox->payload['audience']);
    }

    public function test_edit_link_for_canceled_booking_redirects_to_signed_view_page(): void
    {
        [$customer, $booking] = $this->booking();
        $booking->update(['status' => 'canceled']);
        $urls = app(BookingPublicUrlService::class);

        $this->get($urls->edit($booking, 'it'))
            ->assertRedirect($urls->view($booking, 'it'));
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

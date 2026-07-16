<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingWalkIn;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Mockery;
use Tests\TestCase;

class BookingWalkInTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_seated_booking_without_customer_data(): void
    {
        config()->set('tenant.customer_languages', 'it,en');
        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('slots')->andReturn(['20:00' => ['label' => '20:00', 'meal' => 'cena']]);
        $service->shouldReceive('timeslotStats')->andReturn([]);
        $service->shouldReceive('ensureCapacity')->once()->andReturnNull();
        $this->app->instance(BookingService::class, $service);

        Livewire::test(BookingWalkIn::class)
            ->set('booking_date', now()->toDateString())->set('booking_time', '20:00')
            ->set('pax', 3)->set('lang', 'en')->set('note', 'Arrivati senza prenotazione')
            ->call('save')->assertHasNoErrors();

        $booking = Booking::firstOrFail();
        $this->assertNull($booking->customer_id);
        $this->assertSame('walk-in', $booking->source);
        $this->assertSame('seated', $booking->status);
        $this->assertSame('en', $booking->language);
        $this->assertNotNull($booking->seated_at);
        $this->assertSame(3, $booking->pax);
    }

    public function test_pax_must_be_positive(): void
    {
        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('slots')->andReturn([]);
        $service->shouldReceive('timeslotStats')->andReturn([]);
        $this->app->instance(BookingService::class, $service);

        Livewire::test(BookingWalkIn::class)->set('pax', 0)->call('save')->assertHasErrors(['pax']);
        $this->assertSame(0, Booking::count());
    }

    public function test_table_picker_can_confirm_or_discard_its_working_selection(): void
    {
        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('slots')->andReturn([]);
        $service->shouldReceive('timeslotStats')->andReturn([]);
        $service->shouldReceive('availableTables')->andReturn(collect());
        $this->app->instance(BookingService::class, $service);

        $component = Livewire::test(BookingWalkIn::class)
            ->set('booking_time', '20:00')
            ->set('selectedTablesIds', ['table-one'])
            ->call('openTablePicker')
            ->set('tablePickerSelection', ['table-two'])
            ->call('closeTablePicker')
            ->assertSet('selectedTablesIds', ['table-one'])
            ->assertSet('tablePickerSelection', ['table-one'])
            ->assertSet('tablePickerOpen', false);

        $component->call('openTablePicker')->set('tablePickerSelection', ['table-two'])
            ->call('confirmTables')->assertSet('selectedTablesIds', ['table-two'])
            ->assertSet('tablePickerOpen', false);
    }

    public function test_table_picker_requires_a_booking_time(): void
    {
        $service = Mockery::mock(BookingService::class);
        $service->shouldReceive('slots')->andReturn([]);
        $service->shouldReceive('timeslotStats')->andReturn([]);
        $this->app->instance(BookingService::class, $service);

        Livewire::test(BookingWalkIn::class)->call('openTablePicker')
            ->assertHasErrors(['booking_time'])
            ->assertSet('tablePickerOpen', false);
    }
}

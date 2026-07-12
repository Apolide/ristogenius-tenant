<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingIndex;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingIndexShowAllTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_all_displays_lunch_dinner_and_historical_bookings_for_selected_date(): void
    {
        $selectedDate = now()->addDays(10)->toDateString();
        $customers = collect([
            ['Pranzo', 'Cliente'],
            ['Cena', 'Cliente'],
            ['Storico', 'Cliente'],
        ])->map(fn (array $name, int $index) => Customer::create([
            'firstname' => $name[0],
            'lastname' => $name[1],
            'display_name' => implode(' ', $name),
            'email' => "booking-filter-{$index}@example.test",
            'phone' => '+39333000000'.$index,
            'registration_source' => 'test',
            'lang' => 'it',
        ]));

        foreach ([['12:30', 'accepted'], ['20:30', 'accepted'], ['21:00', 'finalized']] as $index => [$time, $status]) {
            Booking::create([
                'customer_id' => $customers[$index]->id,
                'booking_date' => $selectedDate,
                'booking_time' => $time,
                'pax' => 2,
                'status' => $status,
                'source' => 'backoffice',
                'language' => 'it',
            ]);
        }

        Livewire::test(BookingIndex::class)
            ->set('date', $selectedDate)
            ->set('meal', 'pranzo')
            ->set('showHistory', false)
            ->call('toggleShowAllToday')
            ->assertSet('date', $selectedDate)
            ->assertSet('meal', 'all')
            ->assertSet('showHistory', true)
            ->assertSee('Pranzo Cliente')
            ->assertSee('Cena Cliente')
            ->assertSee('Storico Cliente');
    }
}

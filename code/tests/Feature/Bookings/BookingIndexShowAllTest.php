<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingIndex;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\MarketingForm;
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

    public function test_event_booking_displays_event_badge_with_form_name_tooltip(): void
    {
        $date = now()->addWeek()->toDateString();
        $customer = Customer::create([
            'firstname' => 'Mario',
            'lastname' => 'Rossi',
            'display_name' => 'Mario Rossi',
            'email' => 'event-booking@example.test',
            'registration_source' => 'test',
            'lang' => 'it',
        ]);
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'booking_date' => $date,
            'booking_time' => '20:30',
            'pax' => 4,
            'status' => 'accepted',
            'source' => 'public-form',
            'language' => 'it',
        ]);
        $form = MarketingForm::create([
            'type' => 'event',
            'slug' => 'festa-pugliese',
            'translations' => ['it' => ['title' => 'Festa pugliese']],
            'enabled_languages' => ['it'],
            'is_active' => true,
        ]);
        $form->submissions()->create([
            'booking_id' => $booking->id,
            'language' => 'it',
            'field_snapshot' => [],
            'payload' => [],
        ]);

        Livewire::test(BookingIndex::class)
            ->set('date', $date)
            ->set('meal', 'all')
            ->assertSee(__('bookings.show.event'))
            ->assertSeeHtml('title="Festa pugliese"');
    }
}

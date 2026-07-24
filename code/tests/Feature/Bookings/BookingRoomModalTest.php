<?php

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\BookingRoomIndex;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\RoomTable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookingRoomModalTest extends TestCase
{
    use RefreshDatabase;

    private Room $room;

    private RoomTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->room = Room::query()->create([
            'name' => 'Sala test',
            'active' => true,
            'capacity' => 30,
        ]);
        $this->table = $this->createTable($this->room, 'T1');
    }

    public function test_it_assigns_an_unassigned_booking_and_removes_it_from_the_assignable_list(): void
    {
        $booking = $this->createBooking('accepted');

        Livewire::test(BookingRoomIndex::class)
            ->set('date', $booking->booking_date->toDateString())
            ->set('roomId', $this->room->id)
            ->assertSeeHtml('assignable-booking-'.$booking->id)
            ->call('assignBookingToTable', $booking->id, $this->table->id)
            ->assertHasNoErrors()
            ->assertDontSeeHtml('assignable-booking-'.$booking->id);

        $this->assertDatabaseHas('booking_room_table', [
            'booking_id' => $booking->id,
            'room_table_id' => $this->table->id,
        ]);
    }

    public function test_assigning_the_same_booking_twice_is_idempotent(): void
    {
        $booking = $this->createBooking('accepted');
        $component = Livewire::test(BookingRoomIndex::class)
            ->set('date', $booking->booking_date->toDateString())
            ->set('roomId', $this->room->id);

        $component->call('assignBookingToTable', $booking->id, $this->table->id)->assertHasNoErrors();
        $component->call('assignBookingToTable', $booking->id, $this->table->id)->assertHasNoErrors();

        $this->assertDatabaseCount('booking_room_table', 1);
    }

    #[DataProvider('detachableStatuses')]
    public function test_it_detaches_bookings_in_a_manageable_status(string $status): void
    {
        $booking = $this->createBooking($status);
        $booking->tables()->attach($this->table);

        Livewire::test(BookingRoomIndex::class)
            ->set('date', $booking->booking_date->toDateString())
            ->set('roomId', $this->room->id)
            ->call('detachBookingFromTable', $booking->id, $this->table->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('booking_room_table', [
            'booking_id' => $booking->id,
            'room_table_id' => $this->table->id,
        ]);
    }

    #[DataProvider('nonDetachableStatuses')]
    public function test_it_rejects_detaching_bookings_in_progress_or_closed(string $status): void
    {
        $booking = $this->createBooking($status);
        $booking->tables()->attach($this->table);

        Livewire::test(BookingRoomIndex::class)
            ->set('date', $booking->booking_date->toDateString())
            ->set('roomId', $this->room->id)
            ->call('detachBookingFromTable', $booking->id, $this->table->id)
            ->assertStatus(422);

        $this->assertDatabaseHas('booking_room_table', [
            'booking_id' => $booking->id,
            'room_table_id' => $this->table->id,
        ]);
    }

    public function test_it_rejects_assigning_a_table_from_another_room(): void
    {
        $booking = $this->createBooking('accepted');
        $otherRoom = Room::query()->create([
            'name' => 'Altra sala',
            'active' => true,
            'capacity' => 20,
        ]);
        $otherTable = $this->createTable($otherRoom, 'T2');

        try {
            Livewire::test(BookingRoomIndex::class)
                ->set('date', $booking->booking_date->toDateString())
                ->set('roomId', $this->room->id)
                ->call('assignBookingToTable', $booking->id, $otherTable->id);
            $this->fail('L’assegnazione a un tavolo di un’altra sala doveva essere rifiutata.');
        } catch (ModelNotFoundException) {
            $this->addToAssertionCount(1);
        }

        $this->assertDatabaseCount('booking_room_table', 0);
    }

    public function test_it_rejects_assigning_a_booking_from_another_date(): void
    {
        $booking = $this->createBooking('accepted', now()->addDays(2)->toDateString());

        try {
            Livewire::test(BookingRoomIndex::class)
                ->set('date', now()->addDay()->toDateString())
                ->set('roomId', $this->room->id)
                ->call('assignBookingToTable', $booking->id, $this->table->id);
            $this->fail('L’assegnazione di una prenotazione di un’altra data doveva essere rifiutata.');
        } catch (ModelNotFoundException) {
            $this->addToAssertionCount(1);
        }

        $this->assertDatabaseCount('booking_room_table', 0);
    }

    public function test_render_data_distinguishes_assignable_and_detachable_bookings(): void
    {
        $unassigned = $this->createBooking('accepted');
        $assigned = $this->createBooking('waiting');
        $seated = $this->createBooking('seated');
        $assigned->tables()->attach($this->table);
        $seated->tables()->attach($this->table);

        $component = Livewire::test(BookingRoomIndex::class)
            ->set('date', $unassigned->booking_date->toDateString())
            ->set('roomId', $this->room->id);

        $roomBookings = collect($component->viewData('roomBookings'))->keyBy('id');
        $roomTables = collect($component->viewData('roomTables'))->keyBy('id');
        $tableBookings = collect($roomTables[$this->table->id]['allBookings'])->keyBy('id');

        $this->assertSame([], $roomBookings[$unassigned->id]['tableIds']);
        $this->assertSame([$this->table->id], $roomBookings[$assigned->id]['tableIds']);
        $this->assertTrue($tableBookings[$assigned->id]['canDetach']);
        $this->assertFalse($tableBookings[$seated->id]['canDetach']);
    }

    #[DataProvider('confirmationActions')]
    public function test_each_booking_action_displays_its_specific_confirmation(
        string $action,
        string $translationKey,
    ): void {
        $booking = $this->createBooking('accepted')->load('customer');

        Livewire::test(BookingRoomIndex::class)
            ->set('date', $booking->booking_date->toDateString())
            ->set('roomId', $this->room->id)
            ->call('openAction', $action, $booking->id)
            ->assertSet('modal', $action)
            ->assertSet('selectedBookingId', $booking->id)
            ->assertSee(__("bookings.actions.confirmations.{$translationKey}.title"))
            ->assertSee(__("bookings.actions.confirmations.{$translationKey}.prompt"))
            ->assertSee($booking->customer->display_name);
    }

    #[DataProvider('terminalStatusActions')]
    public function test_terminal_status_removes_the_booking_from_active_room_data_and_changes_the_map_key(
        string $action,
        string $status,
    ): void {
        $booking = $this->createBooking('accepted');
        $booking->tables()->attach($this->table);

        $component = Livewire::test(BookingRoomIndex::class)
            ->set('date', $booking->booking_date->toDateString())
            ->set('roomId', $this->room->id);

        $initialRoomBookings = collect($component->viewData('roomBookings'))->pluck('id');
        $initialTable = collect($component->viewData('roomTables'))->firstWhere('id', $this->table->id);
        $initialMapKey = $this->roomMapKey($component->html());

        $this->assertContains($booking->id, $initialRoomBookings);
        $this->assertContains($booking->id, collect($initialTable['bookings'])->pluck('id'));

        $component
            ->call('openAction', $action, $booking->id)
            ->call('changeStatus', $status)
            ->assertHasNoErrors();

        $updatedRoomBookings = collect($component->viewData('roomBookings'))->pluck('id');
        $updatedTable = collect($component->viewData('roomTables'))->firstWhere('id', $this->table->id);

        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => $status]);
        $this->assertNotContains($booking->id, $updatedRoomBookings);
        $this->assertNotContains($booking->id, collect($updatedTable['bookings'])->pluck('id'));
        $this->assertNotSame($initialMapKey, $this->roomMapKey($component->html()));
    }

    public static function detachableStatuses(): array
    {
        return [
            'pending' => ['pending'],
            'waiting' => ['waiting'],
            'accepted' => ['accepted'],
        ];
    }

    public static function nonDetachableStatuses(): array
    {
        return [
            'seated' => ['seated'],
            'finalized' => ['finalized'],
            'denied' => ['denied'],
            'canceled' => ['canceled'],
            'no-show' => ['no-show'],
        ];
    }

    public static function confirmationActions(): array
    {
        return [
            'accept' => ['accept', 'accept'],
            'deny' => ['deny', 'deny'],
            'no-show' => ['no-show', 'no_show'],
            'seat' => ['seat', 'seat'],
            'finalize' => ['finalize', 'finalize'],
        ];
    }

    public static function terminalStatusActions(): array
    {
        return [
            'no-show' => ['no-show', 'no-show'],
            'finalized' => ['finalize', 'finalized'],
        ];
    }

    private function createBooking(string $status, ?string $date = null): Booking
    {
        $customer = Customer::query()->create([
            'firstname' => 'Cliente',
            'display_name' => 'Cliente '.$status.' '.Customer::query()->count(),
            'email' => fake()->unique()->safeEmail(),
            'lang' => 'it',
            'registration_source' => 'backoffice',
        ]);

        return Booking::query()->create([
            'customer_id' => $customer->id,
            'booking_date' => $date ?? now()->addDay()->toDateString(),
            'booking_time' => '20:00',
            'pax' => 2,
            'status' => $status,
            'source' => 'backoffice',
            'language' => 'it',
        ]);
    }

    private function createTable(Room $room, string $name): RoomTable
    {
        return RoomTable::query()->create([
            'room_id' => $room->id,
            'name' => $name,
            'type' => 'quadrato',
            'min_people' => 1,
            'max_people' => 4,
        ]);
    }

    private function roomMapKey(string $html): string
    {
        preg_match('/wire:key="(room-map-[^"]+)"/', $html, $matches);

        $this->assertArrayHasKey(1, $matches, 'La chiave Livewire della mappa sala non è presente.');

        return $matches[1];
    }
}

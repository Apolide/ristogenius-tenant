<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Rooms\RoomCreate;
use App\Livewire\Settings\RoomTables\RoomTableCreate;
use App\Models\Room;
use App\Models\RoomTable;
use Database\Seeders\RoomSeeder;
use Database\Seeders\RoomTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RoomSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_and_room_table_seeders_write_to_their_dedicated_settings_tables(): void
    {
        $this->seed(RoomSeeder::class);
        $this->seed(RoomTableSeeder::class);

        $this->assertDatabaseHas('settings_rooms', [
            'name' => 'Interna',
            'capacity' => 63,
        ]);
        $this->assertDatabaseHas('settings_rooms', [
            'name' => 'Dehor',
            'capacity' => 18,
        ]);

        $internalRoom = Room::query()->where('name', 'Interna')->firstOrFail();

        $this->assertSame(16, $internalRoom->tables()->count());
        $this->assertDatabaseHas('settings_room_tables', [
            'room_id' => $internalRoom->id,
            'name' => '1',
            'type' => 'quadrato',
            'min_people' => 2,
            'max_people' => 3,
        ]);
    }

    public function test_room_component_saves_rooms_to_settings_rooms_table(): void
    {
        Livewire::test(RoomCreate::class)
            ->call('createRoom')
            ->set('name', 'Terrazza')
            ->set('active', true)
            ->set('service_charge', 3.5)
            ->set('service_charge_percentage', 5)
            ->set('order', 3)
            ->set('capacity', 24)
            ->set('smoking_allowed', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings_rooms', [
            'name' => 'Terrazza',
            'capacity' => 24,
            'smoking_allowed' => true,
        ]);
    }

    public function test_room_table_component_saves_tables_to_settings_room_tables_table(): void
    {
        $room = Room::query()->create([
            'name' => 'Sala Test',
            'active' => true,
            'capacity' => 20,
        ]);

        Livewire::test(RoomTableCreate::class)
            ->call('createRoomTable')
            ->set('name', 'T1')
            ->set('type', 'rettangolare')
            ->set('room_id', $room->id)
            ->set('min_people', 2)
            ->set('max_people', 6)
            ->call('save')
            ->assertHasNoErrors();

        $table = RoomTable::query()->where('name', 'T1')->firstOrFail();

        $this->assertSame($room->id, $table->room_id);
        $this->assertSame('rettangolare', $table->type);
        $this->assertSame(2, $table->min_people);
        $this->assertSame(6, $table->max_people);
        $this->assertSame('free', $table->status);
    }
}

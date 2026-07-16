<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomTable;
use Illuminate\Database\Seeder;

class RoomTableSeeder extends Seeder
{
    /**
     * Seed the default room tables from the provided template.
     */
    public function run(): void
    {
        $tables = [
            ['name' => '1', 'type' => 'quadrato', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 3, 'x' => 140, 'y' => 540, 'w' => 78, 'h' => 78],
            ['name' => '2', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 200, 'y' => 540, 'w' => 78, 'h' => 78],
            ['name' => '3', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 4, 'max_people' => 6, 'x' => 200, 'y' => 460, 'w' => 78, 'h' => 78],
            ['name' => '4', 'type' => 'quadrato', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 3, 'x' => 220, 'y' => 380, 'w' => 78, 'h' => 78],
            ['name' => '5', 'type' => 'quadrato', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 3, 'x' => 160, 'y' => 380, 'w' => 78, 'h' => 78],
            ['name' => '6', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 4, 'max_people' => 6, 'x' => 200, 'y' => 280, 'w' => 78, 'h' => 78],
            ['name' => '7', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 4, 'max_people' => 6, 'x' => 200, 'y' => 180, 'w' => 78, 'h' => 78],
            ['name' => '8', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 4, 'max_people' => 6, 'x' => 200, 'y' => 80, 'w' => 78, 'h' => 78],
            ['name' => '9', 'type' => 'circolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 220, 'y' => 0, 'w' => 78, 'h' => 78],
            ['name' => '10', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 60, 'w' => 78, 'h' => 78],
            ['name' => '11', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 140, 'w' => 78, 'h' => 78],
            ['name' => '12', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 220, 'w' => 78, 'h' => 78],
            ['name' => '13', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 300, 'w' => 78, 'h' => 78],
            ['name' => '14', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 380, 'w' => 78, 'h' => 78],
            ['name' => '15', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 460, 'w' => 78, 'h' => 78],
            ['name' => '16', 'type' => 'rettangolare', 'room' => 'Interna', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 540, 'w' => 98, 'h' => 78],
            ['name' => '18', 'type' => 'quadrato', 'room' => 'Dehor', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 0, 'w' => 78, 'h' => 78],
            ['name' => '19', 'type' => 'quadrato', 'room' => 'Dehor', 'min_people' => 2, 'max_people' => 4, 'x' => 100, 'y' => 0, 'w' => 78, 'h' => 78],
            ['name' => '29', 'type' => 'quadrato', 'room' => 'Dehor', 'min_people' => 2, 'max_people' => 4, 'x' => 0, 'y' => 100, 'w' => 78, 'h' => 78],
            ['name' => '30', 'type' => 'quadrato', 'room' => 'Dehor', 'min_people' => 2, 'max_people' => 4, 'x' => 100, 'y' => 100, 'w' => 78, 'h' => 78],
            ['name' => '31', 'type' => 'quadrato', 'room' => 'Dehor', 'min_people' => 2, 'max_people' => 4, 'x' => 200, 'y' => 100, 'w' => 78, 'h' => 78],
        ];

        foreach ($tables as $table) {
            $room = Room::query()->where('name', $table['room'])->first();

            if (! $room) {
                continue;
            }

            RoomTable::query()->updateOrCreate(
                [
                    'room_id' => $room->id,
                    'name' => $table['name'],
                ],
                [
                    'type' => $table['type'],
                    'min_people' => $table['min_people'],
                    'max_people' => $table['max_people'],
                    'status' => 'free',
                    'x' => $table['x'],
                    'y' => $table['y'],
                    'w' => $table['w'],
                    'h' => $table['h'],
                    'rotation' => 0,
                ]
            );
        }
    }
}

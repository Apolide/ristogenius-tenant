<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Seed the default rooms from the provided template.
     */
    public function run(): void
    {
        $rooms = [
            [
                'name' => 'Interna',
                'active' => true,
                'service_charge' => 2,
                'service_charge_percentage' => 0,
                'order' => 1,
                'capacity' => 63,
                'smoking_allowed' => false,
            ],
            [
                'name' => 'Dehor',
                'active' => true,
                'service_charge' => 2,
                'service_charge_percentage' => 0,
                'order' => 1,
                'capacity' => 18,
                'smoking_allowed' => false,
            ],
        ];

        foreach ($rooms as $room) {
            Room::query()->updateOrCreate(
                ['name' => $room['name']],
                $room
            );
        }
    }
}

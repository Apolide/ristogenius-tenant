<?php

namespace Tests\Feature\Seeders;

use App\Models\Comuni;
use App\Models\Province;
use App\Models\Region;
use Database\Seeders\LocationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_replace_existing_location_data(): void
    {
        $region = Region::create(['name' => 'Existing region']);
        $province = Province::create(['region_id' => $region->id, 'name' => 'Existing province']);
        Comuni::create(['province_id' => $province->id, 'name' => 'Existing comune']);

        $this->seed(LocationSeeder::class);

        $this->assertDatabaseCount('regions', 1);
        $this->assertDatabaseCount('provinces', 1);
        $this->assertDatabaseCount('comunis', 1);
        $this->assertDatabaseHas('regions', ['name' => 'Existing region']);
        $this->assertDatabaseHas('provinces', ['name' => 'Existing province']);
        $this->assertDatabaseHas('comunis', ['name' => 'Existing comune']);
    }
}

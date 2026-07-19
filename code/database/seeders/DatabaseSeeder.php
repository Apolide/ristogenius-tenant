<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // RUN ONLY ONCE ON PRODUCTION

        $this->call([
            RoleSeeder::class,
        ]);

        $this->call([
            AdminSeeder::class,
        ]);

        $this->call([
            TenantProfileSeeder::class,
        ]);
        $this->call([
            TenantSettingsSeeder::class,
        ]);

        $this->call([
            MarketingBookingFormSeeder::class,
            JobApplicationFormSeeder::class,
            CateringOrderFormSeeder::class,
        ]);

        // php artisan db:seed --class=LocationSeeder
        //
        $this->call([
            LocationSeeder::class,
        ]);

        $this->call([
            DepartmentSeeder::class,
        ]);
        $this->call([
            RoomSeeder::class,
        ]);
        $this->call([
            RoomTableSeeder::class,
        ]);

    }
}

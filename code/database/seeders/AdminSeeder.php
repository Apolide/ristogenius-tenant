<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $newUser = User::query()->updateOrCreate(
            ['email' => 'apolideroma@gmail.com'],
            [
                'name' => 'Simone',
                'region_id' => 13,
                'province_id' => 16,
                'comuni_id' => 6017,
                'password' => Hash::make('!C0PP13TT3!'),
            ]
        );
        $newUser->syncRoles(['admin']);

        $newUser = User::query()->updateOrCreate(
            ['email' => 'iula.anto@gmail.com'],
            [
                'name' => 'Antonio',
                'region_id' => 13,
                'province_id' => 16,
                'comuni_id' => 6017,
                'password' => Hash::make('!C0PP13TT3!'),
            ]
        );
        $newUser->syncRoles(['admin']);
    }
}

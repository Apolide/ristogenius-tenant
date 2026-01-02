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
        $newUser = User::create([
            'name' => 'Simone',
            'email' => 'pellecchia@gmail.com',
            'region_id' => 13,
            'province_id' => 16,
            'comuni_id' => 6017,
            'password' => Hash::make('!C0PP13TT3!'),
        ]);
        $newUser->assignRole('admin');

        $newUser = User::create([
            'name' => 'Antonio',
            'email' => 'iula.anto@gmail.com',
            'region_id' => 13,
            'province_id' => 16,
            'comuni_id' => 6017,
            'password' => Hash::make('!C0PP13TT3!'),
        ]);
        $newUser->assignRole('admin');
    }
}
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            ['name' => 'Marco Rossi', 'email' => 'operator1@ristopilot.test', 'phone' => '+393331110201', 'role' => 'operator'],
            ['name' => 'Laura Bianchi', 'email' => 'operator2@ristopilot.test', 'phone' => '+393331110202', 'role' => 'operator'],
            ['name' => 'Paolo Verdi', 'email' => 'operator3@ristopilot.test', 'phone' => '+393331110203', 'role' => 'operator'],
            ['name' => 'Giulia Romano', 'email' => 'manager@ristopilot.test', 'phone' => '+393331110204', 'role' => 'manager'],
        ];

        foreach ($employees as $employee) {
            $role = $employee['role'];
            unset($employee['role']);

            $user = User::query()->updateOrCreate(
                ['email' => $employee['email']],
                $employee + [
                    'lang' => 'it',
                    'password' => Hash::make(Str::random(40)),
                    'enabled' => true,
                    'email_verified_at' => now(),
                    'activated_at' => now(),
                ]
            );
            $user->syncRoles([$role]);
        }
    }
}

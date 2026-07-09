<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the application's default departments.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Accoglienza', 'production' => false],
            ['name' => 'Bar', 'production' => true],
            ['name' => 'Cucina', 'production' => true],
            ['name' => 'Pizzeria', 'production' => true],
        ];

        foreach ($departments as $department) {
            Department::query()->updateOrCreate(
                ['name' => $department['name']],
                [
                    'production' => $department['production'],
                    'use_printer' => false,
                    'printer_number' => null,
                ]
            );
        }
    }
}

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
            ['name' => 'Accoglienza', 'production' => false, 'translations' => ['en' => 'Reception', 'de' => 'Empfang']],
            ['name' => 'Bar', 'production' => true, 'translations' => ['en' => 'Bar', 'de' => 'Bar']],
            ['name' => 'Cucina', 'production' => true, 'translations' => ['en' => 'Kitchen', 'de' => 'Küche']],
            ['name' => 'Pizzeria', 'production' => true, 'translations' => ['en' => 'Pizzeria', 'de' => 'Pizzeria']],
        ];

        foreach ($departments as $department) {
            $model = Department::query()->updateOrCreate(
                ['name' => $department['name']],
                [
                    'production' => $department['production'],
                    'use_printer' => false,
                    'printer_number' => null,
                ]
            );
            $model->update(['name_translations' => array_replace($department['translations'], $model->name_translations ?? [])]);
        }
    }
}

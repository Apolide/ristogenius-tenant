<?php

namespace Database\Seeders;

use App\Models\TenantProfile;
use Illuminate\Database\Seeder;

class TenantProfileSeeder extends Seeder
{
    public function run(): void
    {
        TenantProfile::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => config('tenant.name', config('app.name')),
                'company_name' => 'Ristorante Test Srl',
                'city' => 'Lecce',
                'province' => 'LE',
                'postcode' => '73100',
                'address' => 'Via Roma 1',
            ]
        );
    }
}

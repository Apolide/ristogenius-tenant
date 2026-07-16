<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Personnel\PersonnelPermissionsService;
use Illuminate\Database\Seeder;

class PersonnelPermissionsSeeder extends Seeder
{
    public function run(PersonnelPermissionsService $permissions): void
    {
        $permissionModels = $permissions->ensurePermissionsExist();

        User::query()
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'admin'))
            ->each(fn (User $user) => $user->syncPermissions($permissionModels));
    }
}

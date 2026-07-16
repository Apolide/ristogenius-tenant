<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\Personnel\PersonnelService;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Role::findOrCreate('admin', 'web');

        foreach (PersonnelService::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }
    
        //$roleSupplier = Role::create(['name' => 'supplier']);

        // $permissionManage = Permission::create(['name' => 'manage']);
        // $roleManager->givePermissionTo($permissionManage);

        // $roleBackoffice = Role::create(['name' => 'Backoffice']);
        // $permissionManage = Permission::create(['name' => 'manage bookings']);
        // $roleBackoffice->givePermissionTo($permissionManage);
        
    }
}

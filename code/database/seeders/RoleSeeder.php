<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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
    
        //$roleSupplier = Role::create(['name' => 'supplier']);

        // $permissionManage = Permission::create(['name' => 'manage']);
        // $roleManager->givePermissionTo($permissionManage);

        // $roleBackoffice = Role::create(['name' => 'Backoffice']);
        // $permissionManage = Permission::create(['name' => 'manage bookings']);
        // $roleBackoffice->givePermissionTo($permissionManage);
        
    }
}

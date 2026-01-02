<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $roleAdmin = Role::create(['name' => 'admin']);
    
        //$roleSupplier = Role::create(['name' => 'supplier']);

        // $permissionManage = Permission::create(['name' => 'manage']);
        // $roleManager->givePermissionTo($permissionManage);

        // $roleBackoffice = Role::create(['name' => 'Backoffice']);
        // $permissionManage = Permission::create(['name' => 'manage bookings']);
        // $roleBackoffice->givePermissionTo($permissionManage);
        
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'funcionario']);
        $role4 = Role::create(['name' => 'usuario']);

        // Permission::create(['name'=>'home'])->assignRole($role1);
        Permission::create(['name' => 'home'])->syncRoles([$role1, $role2, $role4]);
        Permission::create(['name' => 'users.index'])->syncRoles([$role1]);
        Permission::create(['name' => 'users.create'])->syncRoles([$role1]);
        Permission::create(['name' => 'users.update'])->syncRoles([$role1]);
        
        //PERMISSIONS ROUTES
        Permission::create(['name' => 'permissions.index'])->syncRoles([$role1]);
        Permission::create(['name' => 'permissions.create'])->syncRoles([$role1]);
        Permission::create(['name' => 'permissions.edit'])->syncRoles([$role1]);
        Permission::create(['name' => 'permissions.delete'])->syncRoles([$role1]);

        //NOTIFICACION ROUTES
        Permission::create(['name' => 'notificacion.index'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'notificacion.create'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'notificacion.edit'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'notificacion.destroy'])->syncRoles([$role1, $role2]);
        //ROLES ROUTES
        Permission::create(['name' => 'roles.index'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'roles.create'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'roles.edit'])->syncRoles([$role1, $role2]);
        Permission::create(['name' => 'roles.destroy'])->syncRoles([$role1, $role2]);
    }
}

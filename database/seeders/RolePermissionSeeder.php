<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Permission;
use App\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'api';

        // ===========================
        // 1. Permissions
        // ===========================
        Permission::firstOrCreate(['name' => 'articles.create',   'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'articles.edit'  ,   'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'articles.delete',   'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'articles.view'  ,   'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'articles.like'  ,   'guard_name' => $guard]);

        Permission::firstOrCreate(['name' => 'comments.create', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'comments.edit'  , 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'comments.delete', 'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'comments.like'  , 'guard_name' => $guard]);

        Permission::firstOrCreate(['name' => 'users.show'   ,    'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'users.store'  ,    'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'users.update' ,    'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'users.delete' ,    'guard_name' => $guard]);
        
        Permission::firstOrCreate(['name' => 'roles.show'   ,    'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'roles.create' ,    'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'roles.update' ,    'guard_name' => $guard]);
        Permission::firstOrCreate(['name' => 'roles.delete' ,    'guard_name' => $guard]);

        // ===========================
        // 2. Roles
        // ===========================
        $user  = Role::firstOrCreate(['name' => 'user'  ,   'guard_name' => $guard]);
        $writer= Role::firstOrCreate(['name' => 'writer',   'guard_name' => $guard]);
        $admin = Role::firstOrCreate(['name' => 'admin' ,   'guard_name' => $guard]);

        // ===========================
        // 3. Assign Permission ke Role
        // ===========================

        // Role: user
        $user->syncPermissions([
            'articles.like'  ,
            'articles.view'  ,
            'comments.create',
            'comments.edit'  ,
            'comments.like'  ,
            'comments.delete'

        ]);

        // Role: writer
        $writer->syncPermissions([
            'articles.create',
            'articles.like'  ,
            'articles.view'  ,
            'articles.edit'  ,
            'articles.delete',
            'comments.create',
            'comments.edit'  ,
            'comments.like'  ,
            'comments.delete'
        ]);

        // Role: admin (all)
        $admin->syncPermissions(Permission::all());

    }
}

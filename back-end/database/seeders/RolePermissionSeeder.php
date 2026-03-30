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
        // 1. Grouped Permissions
        // ===========================
        $permissions = [
            'articles' => ['create', 'update', 'delete', 'view', 'like'],
            'comments' => ['create', 'update', 'delete', 'like'],
            'users'    => ['show', 'create', 'update', 'delete'],
            'roles'    => ['show', 'create', 'update', 'delete'],
            'menu'     => ['home'],
        ];

        foreach ($permissions as $group => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "$group.$action",
                    'guard_name' => $guard
                ]);
            }
        }

        // ===========================
        // 2. Roles
        // ===========================
        $user   = Role::firstOrCreate(['name' => 'user',   'guard_name' => $guard]);
        $writer = Role::firstOrCreate(['name' => 'writer', 'guard_name' => $guard]);
        $admin  = Role::firstOrCreate(['name' => 'admin',  'guard_name' => $guard]);

        // ===========================
        // 3. Assign Permissions to Roles
        // ===========================

        $user->syncPermissions([
            'menu.home',
            'articles.create',
            'articles.view',
            'articles.like',
            'comments.create',
            'comments.update',
            'comments.like',
            'comments.delete'
        ]);

        $writer->syncPermissions([
            'menu.home',
            'articles.create',
            'articles.update',
            'articles.delete',
            'articles.view',
            'articles.like',
            'comments.create',
            'comments.update',
            'comments.like',
            'comments.delete'
        ]);

        // Admin dapat semua
        $admin->syncPermissions(Permission::all());
    }
}

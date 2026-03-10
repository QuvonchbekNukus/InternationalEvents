<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = Permission::query()->pluck('name');

        $roles = [
            'super-admin' => $permissions->all(),
            'admin' => [
                'view users',
                'create users',
                'edit users',
                'view departments',
                'create departments',
                'edit departments',
                'delete departments',
                'view ranks',
                'create ranks',
                'edit ranks',
                'delete ranks',
            ],
            'operator' => [
                'view users',
                'view departments',
                'view ranks',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}

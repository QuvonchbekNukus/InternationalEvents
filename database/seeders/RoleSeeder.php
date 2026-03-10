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
                'view own users',
                'edit own users',
                'view own documents',
                'edit own documents',
                'view own events',
                'edit own events',
                'view own agreements',
                'edit own agreements',
                'view own visits',
                'edit own visits',
            ],
            'operator' => [
                'view own users',
                'edit own users',
                'view own documents',
                'edit own documents',
                'view own events',
                'edit own events',
                'view own agreements',
                'edit own agreements',
                'view own visits',
                'edit own visits',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}

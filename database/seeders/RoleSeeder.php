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
                'view countries',
                'create countries',
                'edit countries',
                'delete countries',
                'view agreements',
                'create agreements',
                'edit agreements',
                'delete agreements',
                'view agreement types',
                'create agreement types',
                'edit agreement types',
                'delete agreement types',
                'view agreement directions',
                'create agreement directions',
                'edit agreement directions',
                'delete agreement directions',
                'view organization types',
                'create organization types',
                'edit organization types',
                'delete organization types',
                'view partner organizations',
                'create partner organizations',
                'edit partner organizations',
                'delete partner organizations',
                'view partner contacts',
                'create partner contacts',
                'edit partner contacts',
                'delete partner contacts',
                'view visits',
                'create visits',
                'edit visits',
                'delete visits',
                'view visit types',
                'create visit types',
                'edit visit types',
                'delete visit types',
            ],
            'operator' => [
                'view users',
                'view departments',
                'view ranks',
                'view countries',
                'view agreements',
                'view agreement types',
                'view agreement directions',
                'view organization types',
                'view partner organizations',
                'view partner contacts',
                'view visits',
                'view visit types',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}

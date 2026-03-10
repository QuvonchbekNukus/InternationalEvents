<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view users',
            'view own users',
            'create users',
            'edit users',
            'edit own users',
            'delete users',
            'view departments',
            'create departments',
            'edit departments',
            'delete departments',
            'view ranks',
            'create ranks',
            'edit ranks',
            'delete ranks',
            'view activity logs',
            'manage role permissions',
            'view countries',
            'create countries',
            'edit countries',
            'delete countries',
            'view documents',
            'view own documents',
            'create documents',
            'edit documents',
            'edit own documents',
            'delete documents',
            'view document types',
            'create document types',
            'edit document types',
            'delete document types',
            'view events',
            'view own events',
            'create events',
            'edit events',
            'edit own events',
            'delete events',
            'view event types',
            'create event types',
            'edit event types',
            'delete event types',
            'view agreements',
            'view own agreements',
            'create agreements',
            'edit agreements',
            'edit own agreements',
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
            'view own visits',
            'create visits',
            'edit visits',
            'edit own visits',
            'delete visits',
            'view visit types',
            'create visit types',
            'edit visit types',
            'delete visit types',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

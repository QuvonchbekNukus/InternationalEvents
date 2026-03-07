<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        $roles = [
            'super-admin',
            'admin',
            'operator',
        ];

        foreach ($roles as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }
    }
}

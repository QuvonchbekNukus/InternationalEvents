<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolePermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_authorized_user_can_view_only_roles_before_selecting_role(): void
    {
        $managePermission = Permission::findOrCreate('manage role permissions', 'web');
        Permission::findOrCreate('view users', 'web');
        Permission::findOrCreate('edit users', 'web');

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->givePermissionTo($managePermission);

        Role::findOrCreate('operator', 'web');

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $response = $this->actingAs($user)->get(route('role-permissions.index'));

        $response->assertOk();
        $response->assertSee("Ruxsatlarni boshqarish");
        $response->assertSee("Super Admin");
        $response->assertDontSee("Foydalanuvchilar");
        $response->assertDontSee("manage role permissions");
    }

    public function test_permissions_are_visible_after_role_is_selected(): void
    {
        $managePermission = Permission::findOrCreate('manage role permissions', 'web');
        Permission::findOrCreate('view users', 'web');
        Permission::findOrCreate('edit users', 'web');

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->givePermissionTo($managePermission);

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $response = $this->actingAs($user)->get(route('role-permissions.index', ['role' => $superAdminRole->name]));

        $response->assertOk();
        $response->assertSee("Foydalanuvchilar");
        $response->assertSee("manage role permissions");
    }

    public function test_authorized_user_can_update_role_permissions(): void
    {
        $managePermission = Permission::findOrCreate('manage role permissions', 'web');
        $viewUsersPermission = Permission::findOrCreate('view users', 'web');
        $editUsersPermission = Permission::findOrCreate('edit users', 'web');

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->givePermissionTo($managePermission);

        $operatorRole = Role::findOrCreate('operator', 'web');
        $operatorRole->givePermissionTo($editUsersPermission);

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $response = $this->actingAs($user)->put(route('role-permissions.update', $operatorRole), [
            'permissions' => [$viewUsersPermission->name],
        ]);

        $response->assertRedirect(route('role-permissions.index', ['role' => $operatorRole->name]));

        $operatorRole->refresh();

        $this->assertTrue($operatorRole->hasPermissionTo($viewUsersPermission));
        $this->assertFalse($operatorRole->hasPermissionTo($editUsersPermission));
    }

    public function test_super_admin_role_can_access_page_even_without_explicit_permission_binding(): void
    {
        Role::findOrCreate('operator', 'web');

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $response = $this->actingAs($user)->get(route('role-permissions.index'));

        $response->assertOk();
        $response->assertSee("Ruxsatlarni boshqarish");
        $response->assertDontSee("Foydalanuvchilar");
    }
}

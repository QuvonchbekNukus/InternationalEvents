<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertSee('Rollar va ruxsatlar');
        $response->assertSee('Super Admin');
        $response->assertDontSee('Foydalanuvchilar');
        $response->assertDontSee('manage role permissions');
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
        $response->assertSee('Foydalanuvchilar');
        $response->assertSee('manage role permissions');
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
        $response->assertSee('Rollar va ruxsatlar');
        $response->assertDontSee('Foydalanuvchilar');
    }

    public function test_authorized_user_can_create_new_role(): void
    {
        Permission::findOrCreate('manage role permissions', 'web');

        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->givePermissionTo('manage role permissions');

        Role::findOrCreate('operator', 'web');

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $response = $this->actingAs($user)->post(route('role-permissions.store'), [
            'name' => 'moderator',
        ]);

        $response->assertRedirect(route('role-permissions.index', ['role' => 'moderator']));

        $this->assertDatabaseHas('roles', [
            'name' => 'moderator',
            'guard_name' => 'web',
        ]);
    }

    public function test_role_store_rejects_invalid_name_for_unauthorized_user(): void
    {
        Role::findOrCreate('operator', 'web');

        $response = $this->actingAs(User::factory()->create())->post(route('role-permissions.store'), [
            'name' => 'hacker-role',
        ]);

        $response->assertForbidden();
    }

    public function test_role_store_validates_name_format(): void
    {
        Permission::findOrCreate('manage role permissions', 'web');
        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->givePermissionTo('manage role permissions');
        Role::findOrCreate('operator', 'web');

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $response = $this->actingAs($user)->post(route('role-permissions.store'), [
            'name' => 'Not Valid',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_can_rename_non_system_role(): void
    {
        Permission::findOrCreate('manage role permissions', 'web');
        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->givePermissionTo('manage role permissions');
        $operatorRole = Role::findOrCreate('operator', 'web');

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $response = $this->actingAs($user)->patch(route('role-permissions.rename', $operatorRole), [
            'new_name' => 'operator-renamed',
        ]);

        $response->assertRedirect(route('role-permissions.index', ['role' => 'operator-renamed']));

        $this->assertDatabaseHas('roles', [
            'name' => 'operator-renamed',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseMissing('roles', [
            'name' => 'operator',
            'guard_name' => 'web',
        ]);
    }

    public function test_cannot_rename_super_admin_role(): void
    {
        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('operator', 'web');

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $this->actingAs($user)
            ->patch(route('role-permissions.rename', $superAdminRole), ['new_name' => 'root'])
            ->assertForbidden();
    }

    public function test_cannot_destroy_super_admin_role(): void
    {
        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('operator', 'web');

        $user = User::factory()->create();
        $user->assignRole($superAdminRole);

        $this->actingAs($user)
            ->delete(route('role-permissions.destroy', $superAdminRole))
            ->assertForbidden();
    }

    public function test_can_destroy_role_and_detach_users(): void
    {
        Permission::findOrCreate('manage role permissions', 'web');
        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdminRole->givePermissionTo('manage role permissions');
        Role::findOrCreate('operator', 'web');

        $tempRole = Role::create([
            'name' => 'temp-role',
            'guard_name' => 'web',
        ]);

        $admin = User::factory()->create();
        $admin->assignRole($superAdminRole);

        $member = User::factory()->create();
        $member->assignRole($tempRole);

        $tempRoleId = $tempRole->id;

        $this->actingAs($admin)
            ->delete(route('role-permissions.destroy', $tempRole))
            ->assertRedirect(route('role-permissions.index'));

        $this->assertDatabaseMissing('roles', ['id' => $tempRoleId]);

        $member->refresh();
        $this->assertFalse($member->roles()->where('roles.id', $tempRoleId)->exists());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Country;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SuperAdminNotificationMirrorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_super_admin_receives_mirrored_copy_when_other_user_gets_notification(): void
    {
        $createPermission = Permission::findOrCreate('create agreements', 'web');
        $viewOwnPermission = Permission::findOrCreate('view own agreements', 'web');

        $superAdminRole = Role::findOrCreate('super-admin', 'web');

        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole($superAdminRole);

        $actor = User::factory()->create();
        $actor->givePermissionTo($createPermission);

        $responsibleUser = User::factory()->create(['is_active' => true]);
        $responsibleUser->givePermissionTo($viewOwnPermission);

        $country = Country::create([
            'name_ru' => 'Kazahstan',
            'name_uz' => "Qozog'iston",
            'iso2' => 'KZ',
            'iso3' => 'KAZ',
            'cooperation_status' => 'faol',
        ]);

        $this->actingAs($actor)
            ->post(route('agreements.store'), [
                'agreement_number' => 'MG-TEST-SA-001',
                'title_ru' => 'Test',
                'title_uz' => 'Sinov',
                'country_id' => $country->id,
                'status' => 'draft',
                'responsible_user_id' => $responsibleUser->id,
            ])
            ->assertRedirect(route('agreements.index'));

        $agreement = Agreement::firstOrFail();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $responsibleUser->id,
            'related_type' => Agreement::class,
            'related_id' => $agreement->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $superAdmin->id,
            'related_type' => Agreement::class,
            'related_id' => $agreement->id,
        ]);

        $this->assertSame(2, Notification::count());
    }

    public function test_no_duplicate_mirror_for_super_admin_recipient(): void
    {
        $superAdminRole = Role::findOrCreate('super-admin', 'web');
        $superAdmin = User::factory()->create(['is_active' => true]);
        $superAdmin->assignRole($superAdminRole);

        Notification::create([
            'user_id' => $superAdmin->id,
            'title' => 'Test',
            'message' => 'Xabar',
            'type' => 'info',
            'related_type' => null,
            'related_id' => null,
        ]);

        $this->assertSame(1, Notification::count());
    }
}

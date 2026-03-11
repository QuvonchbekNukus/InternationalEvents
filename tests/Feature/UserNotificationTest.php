<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Country;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_responsible_user_receives_notification_and_can_open_related_record(): void
    {
        $createPermission = Permission::findOrCreate('create agreements', 'web');
        $viewOwnPermission = Permission::findOrCreate('view own agreements', 'web');

        $actor = User::factory()->create();
        $actor->givePermissionTo($createPermission);

        $responsibleUser = User::factory()->create();
        $responsibleUser->givePermissionTo($viewOwnPermission);

        $otherUser = User::factory()->create();

        $country = Country::create([
            'name_ru' => 'Kazahstan',
            'name_uz' => "Qozog'iston",
            'name_cryl' => 'Qozogiston',
            'iso2' => 'KZ',
            'iso3' => 'KAZ',
            'cooperation_status' => 'faol',
        ]);

        $response = $this
            ->actingAs($actor)
            ->post(route('agreements.store'), [
                'agreement_number' => 'MG-TEST-001',
                'title_ru' => 'Test soglashenie',
                'title_uz' => 'Sinov kelishuvi',
                'title_cryl' => 'Sinov kelishuvi',
                'country_id' => $country->id,
                'status' => 'draft',
                'responsible_user_id' => $responsibleUser->id,
            ]);

        $response->assertRedirect(route('agreements.index'));

        $agreement = Agreement::firstOrFail();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $responsibleUser->id,
            'title' => 'Yangi kelishuv biriktirildi',
            'related_type' => Agreement::class,
            'related_id' => $agreement->id,
            'is_read' => false,
        ]);

        $notification = Notification::firstOrFail();

        $this->actingAs($responsibleUser)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Shaxsiy notificationlar')
            ->assertSee('Yangi kelishuv biriktirildi')
            ->assertSee('Sinov kelishuvi');

        $this->actingAs($otherUser)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('Yangi kelishuv biriktirildi');

        $this->actingAs($responsibleUser)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('agreements.show', $agreement));

        $this->assertTrue($notification->fresh()->is_read);

        $this->actingAs($responsibleUser)
            ->get(route('agreements.show', $agreement))
            ->assertOk()
            ->assertSee('Sinov kelishuvi')
            ->assertSee("Kelishuv tafsilotlari");
    }
}

<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_dashboard_displays_month_event_calendar_for_users_with_event_access(): void
    {
        $viewEventsPermission = Permission::findOrCreate('view events', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewEventsPermission);

        $country = $this->createCountry('UZ', 'UZB');

        Event::create([
            'title_ru' => 'Strategicheskaya sessiya',
            'title_uz' => 'Strategik sessiya',
            'title_cryl' => 'Strategik sessiya',
            'country_id' => $country->id,
            'start_datetime' => '2026-03-12 09:00:00',
            'end_datetime' => '2026-03-14 18:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('Tadbirlar kalendari');
        $response->assertSee('Mart 2026');
        $response->assertSee('Strategik sessiya');
    }

    public function test_dashboard_only_shows_owned_events_for_users_with_view_own_events_permission(): void
    {
        $viewOwnEventsPermission = Permission::findOrCreate('view own events', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewOwnEventsPermission);

        $otherUser = User::factory()->create();
        $country = $this->createCountry('KZ', 'KAZ');

        Event::create([
            'title_ru' => 'Moiy sobstvenniy event',
            'title_uz' => 'Mening tadbirim',
            'title_cryl' => 'Mening tadbirim',
            'country_id' => $country->id,
            'start_datetime' => '2026-03-18 10:00:00',
            'end_datetime' => '2026-03-18 16:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'responsible_user_id' => $user->id,
            'created_by' => $otherUser->id,
            'updated_by' => $otherUser->id,
        ]);

        Event::create([
            'title_ru' => 'Chuzhoy event',
            'title_uz' => 'Begona tadbir',
            'title_cryl' => 'Begona tadbir',
            'country_id' => $country->id,
            'start_datetime' => '2026-03-19 10:00:00',
            'end_datetime' => '2026-03-19 12:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'responsible_user_id' => $otherUser->id,
            'created_by' => $otherUser->id,
            'updated_by' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('Mening tadbirim');
        $response->assertDontSee('Begona tadbir');
    }

    private function createCountry(string $iso2, string $iso3): Country
    {
        return Country::create([
            'name_ru' => 'Test country '.$iso2,
            'name_uz' => 'Test davlat '.$iso2,
            'name_cryl' => 'Test davlat '.$iso2,
            'iso2' => $iso2,
            'iso3' => $iso3,
            'cooperation_status' => 'faol',
        ]);
    }
}

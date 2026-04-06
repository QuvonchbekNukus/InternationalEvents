<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Notification;
use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use App\Models\Permission;
use App\Models\User;
use App\Services\DateReminderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PartnerContactBirthdayNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_creates_notification_one_day_before_birthday_for_users_with_view_permission(): void
    {
        Carbon::setTestNow('2026-04-02 10:00:00');

        $permission = Permission::findOrCreate('view partner contacts', 'web');

        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->givePermissionTo($permission);

        $other = User::factory()->create(['is_active' => true]);

        $country = $this->createCountry('UZ', 'UZB');
        $organization = PartnerOrganization::create([
            'country_id' => $country->id,
            'name_ru' => 'Org',
            'name_uz' => 'Tashkilot',
            'short_name' => 'T',
            'status' => 'faol',
        ]);

        $contact = PartnerContact::create([
            'partner_organization_id' => $organization->id,
            'full_name_ru' => 'RU',
            'full_name_uz' => 'Sherik kontakt',
            'birthday' => '1990-04-03',
            'position_uz' => 'Maslahatchi',
        ]);

        $service = app(DateReminderNotificationService::class);
        $created = $service->dispatchPartnerContactBirthdayReminders();

        $this->assertSame(1, $created);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $viewer->id,
            'related_type' => PartnerContact::class,
            'related_id' => $contact->id,
            'type' => DateReminderNotificationService::PARTNER_CONTACT_BIRTHDAY_TYPE,
            'is_read' => false,
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $other->id,
            'related_id' => $contact->id,
        ]);

        $notification = Notification::where('user_id', $viewer->id)->firstOrFail();
        $this->assertSame(route('partner-contacts.show', $contact), $notification->resolveTargetUrl());
    }

    public function test_second_run_same_day_does_not_duplicate(): void
    {
        Carbon::setTestNow('2026-04-02 12:00:00');

        $permission = Permission::findOrCreate('view partner contacts', 'web');
        $viewer = User::factory()->create(['is_active' => true]);
        $viewer->givePermissionTo($permission);

        $country = $this->createCountry('KZ', 'KAZ');
        $organization = PartnerOrganization::create([
            'country_id' => $country->id,
            'name_ru' => 'Org',
            'name_uz' => 'Tashkilot',
            'short_name' => 'T',
            'status' => 'faol',
        ]);

        PartnerContact::create([
            'partner_organization_id' => $organization->id,
            'full_name_ru' => 'RU',
            'full_name_uz' => 'Kontakt',
            'birthday' => '1985-04-03',
            'position_uz' => 'X',
        ]);

        $service = app(DateReminderNotificationService::class);
        $this->assertSame(1, $service->dispatchPartnerContactBirthdayReminders());
        $this->assertSame(0, $service->dispatchPartnerContactBirthdayReminders());
        $this->assertSame(1, Notification::count());
    }

    private function createCountry(string $iso2, string $iso3): Country
    {
        return Country::create([
            'name_ru' => 'Test',
            'name_uz' => 'Test',
            'iso2' => $iso2,
            'iso3' => $iso3,
            'cooperation_status' => 'faol',
        ]);
    }
}

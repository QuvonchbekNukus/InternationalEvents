<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use App\Models\Visit;
use App\Services\DateReminderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EventVisitStartReminderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_creates_event_start_notification_for_responsible_user_on_start_day(): void
    {
        Carbon::setTestNow('2026-06-15 09:00:00');

        $user = User::factory()->create(['is_active' => true]);
        $country = $this->createCountry();

        Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'Forum',
            'country_id' => $country->id,
            'start_datetime' => '2026-06-15 10:00:00',
            'end_datetime' => '2026-06-15 18:00:00',
            'responsible_user_id' => $user->id,
            'status' => 'rejada',
        ]);

        $event = Event::firstOrFail();
        $created = app(DateReminderNotificationService::class)->dispatchEventStartReminders();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_type' => Event::class,
            'related_id' => $event->id,
            'type' => DateReminderNotificationService::EVENT_START_TYPE,
            'is_read' => false,
        ]);
    }

    public function test_creates_visit_start_notification_for_responsible_user_on_start_day(): void
    {
        Carbon::setTestNow('2026-07-01 08:00:00');

        $user = User::factory()->create(['is_active' => true]);
        $country = $this->createCountry();

        Visit::create([
            'title_ru' => 'RU',
            'title_uz' => 'Vizit',
            'country_id' => $country->id,
            'start_date' => '2026-07-01',
            'responsible_user_id' => $user->id,
            'status' => 'planned',
        ]);

        $visit = Visit::firstOrFail();
        $created = app(DateReminderNotificationService::class)->dispatchVisitStartReminders();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'related_type' => Visit::class,
            'related_id' => $visit->id,
            'type' => DateReminderNotificationService::VISIT_START_TYPE,
            'is_read' => false,
        ]);
    }

    public function test_second_run_same_day_does_not_duplicate_event_reminder(): void
    {
        Carbon::setTestNow('2026-08-20 12:00:00');

        $user = User::factory()->create(['is_active' => true]);
        $country = $this->createCountry();

        Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'Seminar',
            'country_id' => $country->id,
            'start_datetime' => '2026-08-20 09:00:00',
            'end_datetime' => null,
            'responsible_user_id' => $user->id,
            'status' => 'rejada',
        ]);

        $service = app(DateReminderNotificationService::class);
        $this->assertSame(1, $service->dispatchEventStartReminders());
        $this->assertSame(0, $service->dispatchEventStartReminders());
        $this->assertSame(1, Notification::count());
    }

    public function test_ensure_event_start_reminder_works_without_scheduler_same_afternoon(): void
    {
        Carbon::setTestNow('2026-11-05 16:30:00');

        $user = User::factory()->create(['is_active' => true]);
        $country = $this->createCountry();

        $event = Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'Kechki tadbir',
            'country_id' => $country->id,
            'start_datetime' => '2026-11-05 10:00:00',
            'end_datetime' => '2026-11-05 18:00:00',
            'responsible_user_id' => $user->id,
            'status' => 'rejada',
        ]);

        $service = app(DateReminderNotificationService::class);
        $this->assertSame(1, $service->ensureEventStartReminderFor($event->fresh()));
        $this->assertSame(0, $service->ensureEventStartReminderFor($event->fresh()));
        $this->assertSame(1, Notification::count());
    }

    public function test_event_start_notifies_creator_when_responsible_missing(): void
    {
        Carbon::setTestNow('2026-12-20 10:00:00');

        $creator = User::factory()->create(['is_active' => true]);
        $country = $this->createCountry();

        Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'Yaratuvchi',
            'country_id' => $country->id,
            'start_datetime' => '2026-12-20 11:00:00',
            'end_datetime' => null,
            'responsible_user_id' => null,
            'created_by' => $creator->id,
            'status' => 'rejada',
        ]);

        $event = Event::firstOrFail();
        $created = app(DateReminderNotificationService::class)->dispatchEventStartReminders();

        $this->assertSame(1, $created);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $creator->id,
            'related_id' => $event->id,
            'type' => DateReminderNotificationService::EVENT_START_TYPE,
        ]);
    }

    public function test_event_start_notifies_responsible_and_creator_when_different(): void
    {
        Carbon::setTestNow('2026-12-21 10:00:00');

        $responsible = User::factory()->create(['is_active' => true]);
        $creator = User::factory()->create(['is_active' => true]);
        $country = Country::create([
            'name_ru' => 'Test',
            'name_uz' => 'Test',
            'iso2' => 'T2',
            'iso3' => 'TS2',
            'cooperation_status' => 'faol',
        ]);

        Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'Ikkala',
            'country_id' => $country->id,
            'start_datetime' => '2026-12-21 09:00:00',
            'end_datetime' => null,
            'responsible_user_id' => $responsible->id,
            'created_by' => $creator->id,
            'status' => 'rejada',
        ]);

        $event = Event::firstOrFail();
        $created = app(DateReminderNotificationService::class)->dispatchEventStartReminders();

        $this->assertSame(2, $created);
        $this->assertSame(2, Notification::where('related_id', $event->id)->count());
    }

    public function test_skips_cancelled_event_and_cancelled_visit(): void
    {
        Carbon::setTestNow('2026-09-01 10:00:00');

        $user = User::factory()->create(['is_active' => true]);
        $country = $this->createCountry();

        Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'Off',
            'country_id' => $country->id,
            'start_datetime' => '2026-09-01 11:00:00',
            'end_datetime' => null,
            'responsible_user_id' => $user->id,
            'status' => 'bekorlangan',
        ]);

        Visit::create([
            'title_ru' => 'RU',
            'title_uz' => 'Off',
            'country_id' => $country->id,
            'start_date' => '2026-09-01',
            'responsible_user_id' => $user->id,
            'status' => 'cancelled',
        ]);

        $service = app(DateReminderNotificationService::class);
        $this->assertSame(0, $service->dispatchEventStartReminders());
        $this->assertSame(0, $service->dispatchVisitStartReminders());
    }

    private function createCountry(): Country
    {
        return Country::create([
            'name_ru' => 'Test',
            'name_uz' => 'Test',
            'iso2' => 'T1',
            'iso3' => 'TS1',
            'cooperation_status' => 'faol',
        ]);
    }
}

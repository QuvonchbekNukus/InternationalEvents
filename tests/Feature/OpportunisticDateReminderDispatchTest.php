<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use App\Services\DateReminderNotificationService;
use App\Services\OpportunisticDateReminderDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OpportunisticDateReminderDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Cache::flush();

        parent::tearDown();
    }

    public function test_guest_does_not_trigger_dispatch(): void
    {
        Cache::flush();
        app(OpportunisticDateReminderDispatcher::class)->dispatchIfDue(null);
        $this->assertSame(0, Notification::count());
    }

    public function test_authenticated_scan_creates_due_event_reminder(): void
    {
        Carbon::setTestNow('2026-12-10 11:00:00');
        Cache::flush();

        $user = User::factory()->create(['is_active' => true]);
        $country = Country::create([
            'name_ru' => 'Test',
            'name_uz' => 'Test',
            'iso2' => 'T9',
            'iso3' => 'TS9',
            'cooperation_status' => 'faol',
        ]);

        Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'Bugungi',
            'country_id' => $country->id,
            'start_datetime' => '2026-12-10 09:00:00',
            'end_datetime' => null,
            'responsible_user_id' => $user->id,
            'status' => 'rejada',
        ]);

        app(OpportunisticDateReminderDispatcher::class)->dispatchIfDue($user);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => DateReminderNotificationService::EVENT_START_TYPE,
        ]);
    }

    public function test_throttle_skips_second_scan_until_window_passes(): void
    {
        Carbon::setTestNow('2026-12-11 12:00:00');
        Cache::flush();

        $user = User::factory()->create(['is_active' => true]);
        $country = Country::create([
            'name_ru' => 'Test',
            'name_uz' => 'Test',
            'iso2' => 'T8',
            'iso3' => 'TS8',
            'cooperation_status' => 'faol',
        ]);

        Event::create([
            'title_ru' => 'RU',
            'title_uz' => 'A',
            'country_id' => $country->id,
            'start_datetime' => '2026-12-11 09:00:00',
            'end_datetime' => null,
            'responsible_user_id' => $user->id,
            'status' => 'rejada',
        ]);

        $dispatcher = app(OpportunisticDateReminderDispatcher::class);
        $dispatcher->dispatchIfDue($user);
        $this->assertSame(1, Notification::count());

        Notification::query()->delete();
        $this->assertSame(0, Notification::count());

        $dispatcher->dispatchIfDue($user);

        $this->assertSame(0, Notification::count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Event;
use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Models\Visit;
use Carbon\CarbonImmutable;
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

    public function test_dashboard_displays_active_visits_for_users_with_visit_access(): void
    {
        $viewVisitsPermission = Permission::findOrCreate('view visits', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewVisitsPermission);

        $country = $this->createCountry('TR', 'TUR');

        Visit::create([
            'title_ru' => 'Delegatsiya',
            'title_uz' => 'Xizmat tashrifi',
            'country_id' => $country->id,
            'start_date' => '2026-03-21',
            'end_date' => '2026-03-23',
            'status' => 'ongoing',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('Xizmat tashrifi');
        $response->assertSee('Tashriflar');
    }

    public function test_dashboard_exports_canonical_calendar_fields_and_grouped_filters(): void
    {
        $viewEventsPermission = Permission::findOrCreate('view events', 'web');
        $viewVisitsPermission = Permission::findOrCreate('view visits', 'web');
        $viewPartnerContactsPermission = Permission::findOrCreate('view partner contacts', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewEventsPermission);
        $user->givePermissionTo($viewVisitsPermission);
        $user->givePermissionTo($viewPartnerContactsPermission);

        $country = $this->createCountry('AZ', 'AZE');
        $organization = PartnerOrganization::create([
            'country_id' => $country->id,
            'name_ru' => 'Canonical partner',
            'name_uz' => 'Canonical hamkor',
            'short_name' => 'CH',
            'status' => 'faol',
        ]);

        $event = Event::create([
            'title_ru' => 'Canonical event',
            'title_uz' => 'Canonical tadbir',
            'country_id' => $country->id,
            'start_datetime' => '2026-03-10 09:00:00',
            'end_datetime' => '2026-03-10 18:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $visit = Visit::create([
            'title_ru' => 'Canonical visit',
            'title_uz' => 'Canonical tashrif',
            'country_id' => $country->id,
            'start_date' => '2026-03-11',
            'end_date' => '2026-03-11',
            'status' => 'completed',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $partnerContact = PartnerContact::create([
            'partner_organization_id' => $organization->id,
            'full_name_ru' => 'Canonical contact',
            'full_name_uz' => 'Canonical kontakt',
            'birthday' => '1992-03-12',
            'position_uz' => 'Coordinator',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('data-calendar-filter-group="type"', false);
        $response->assertSee('data-calendar-filter-group="status"', false);
        $response->assertSee('data-calendar-filter-value="all"', false);
        $response->assertSee('data-calendar-filter-value="event"', false);
        $response->assertSee('data-calendar-filter-value="visit"', false);
        $response->assertSee('data-calendar-filter-value="birthday"', false);
        $response->assertSee('data-calendar-filter-value="planned"', false);
        $response->assertSee('data-calendar-filter-value="completed"', false);
        $response->assertSee('role="radio"', false);
        $response->assertSee('"type":"event"', false);
        $response->assertSee('"type":"visit"', false);
        $response->assertSee('"type":"birthday"', false);
        $response->assertSee('"status":"planned"', false);
        $response->assertSee('"status":"completed"', false);
        $response->assertSee('"status":null', false);
        $response->assertSee('"is_recurring":false', false);
        $response->assertSee('"is_recurring":true', false);
        $response->assertSee('"recurrence_type":"yearly"', false);
        $response->assertDontSee('"kind":"event"', false);
        $response->assertDontSee('"state":"planned"', false);
        $response->assertSee("let activeTypeFilter = 'all';", false);
        $response->assertSee("const matchesTypeFilter = (item, typeFilter = activeTypeFilter) => typeFilter === 'all'", false);
        $response->assertSee('const refreshFilteredState = () => {', false);
        $response->assertSee('const addDaysToIsoDate = (isoDate, days = 1) => {', false);
        $response->assertSee('const filteredItemsForDate = (date) => {', false);
        $response->assertSee('data-calendar-endpoint=', false);
        $response->assertSee('const requestCalendar = async (monthKey) => {', false);
        $response->assertDontSee('form.requestSubmit();', false);
        $response->assertDontSee('cursor = nextDay.toISOString().slice(0, 10);', false);
        $response->assertDontSee('const activeTypeFilters = new Set();', false);
        $response->assertSee('"source_id":'.$event->id, false);
        $response->assertSee('"source_id":'.$visit->id, false);
        $response->assertSee('"source_id":'.$partnerContact->id, false);
        $response->assertSee('"start_date":"2026-03-10"', false);
        $response->assertSee('"end_date":"2026-03-10"', false);
        $response->assertSee('"start_date":"2026-03-11"', false);
        $response->assertSee('"end_date":"2026-03-11"', false);
        $response->assertSee('"start_date":"2026-03-12"', false);
        $response->assertSee('"end_date":"2026-03-12"', false);
    }

    public function test_dashboard_calendar_endpoint_returns_async_payload(): void
    {
        $viewEventsPermission = Permission::findOrCreate('view events', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewEventsPermission);

        $country = $this->createCountry('GB', 'GBR');

        Event::create([
            'title_ru' => 'Async event',
            'title_uz' => 'Asinxron tadbir',
            'country_id' => $country->id,
            'start_datetime' => '2026-04-09 09:00:00',
            'end_datetime' => '2026-04-11 18:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson(route('dashboard.calendar', ['month' => '2026-04']));

        $response->assertOk();
        $response->assertJsonPath('eventCalendar.month_key', '2026-04');
        $response->assertJsonPath('eventCalendar.month_label', 'Aprel 2026');
        $response->assertJsonPath('eventCalendar.items.0.title', 'Asinxron tadbir');
        $response->assertJsonStructure([
            'eventCalendar' => [
                'month_key',
                'month_label',
                'prev_url',
                'next_url',
                'count_label',
                'items',
                'weeks',
                'day_lookup',
                'filters',
                'selected_date',
                'texts',
            ],
            'calendarMonthOptions',
            'calendarYearOptions',
        ]);
    }

    public function test_dashboard_keeps_selected_april_month_when_current_day_is_31st(): void
    {
        CarbonImmutable::setTestNow('2026-03-31 12:00:00');

        try {
            $viewEventsPermission = Permission::findOrCreate('view events', 'web');

            $user = User::factory()->create();
            $user->givePermissionTo($viewEventsPermission);

            $country = $this->createCountry('DE', 'DEU');

            Event::create([
                'title_ru' => 'Aprelskiy forum',
                'title_uz' => 'Aprel forumi',
                'country_id' => $country->id,
                'start_datetime' => '2026-04-10 09:00:00',
                'end_datetime' => '2026-04-10 18:00:00',
                'format' => 'offline',
                'status' => 'rejada',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            $response = $this
                ->actingAs($user)
                ->get(route('dashboard', ['month' => '2026-04']));

            $response->assertOk();
            $response->assertSee('Aprel 2026');
            $response->assertSee('Aprel forumi');
            $response->assertDontSee('May 2026');
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_dashboard_splits_multi_day_events_across_week_boundaries(): void
    {
        $viewEventsPermission = Permission::findOrCreate('view events', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewEventsPermission);

        $country = $this->createCountry('FR', 'FRA');

        Event::create([
            'title_ru' => 'Mejdunarodniy forum',
            'title_uz' => 'Xalqaro forum',
            'country_id' => $country->id,
            'start_datetime' => '2026-03-27 09:00:00',
            'end_datetime' => '2026-04-02 18:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('Xalqaro forum');
        $response->assertSee('event-calendar-compact__marker-dot', false);
        $response->assertDontSee('grid-column:', false);
    }

    public function test_dashboard_keeps_multi_week_event_visible_in_each_week_of_the_same_month(): void
    {
        $viewEventsPermission = Permission::findOrCreate('view events', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewEventsPermission);

        $country = $this->createCountry('IT', 'ITA');

        Event::create([
            'title_ru' => 'Nedelnaya sessiya',
            'title_uz' => 'Haftalik sessiya',
            'country_id' => $country->id,
            'start_datetime' => '2026-04-01 09:00:00',
            'end_datetime' => '2026-04-08 18:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson(route('dashboard.calendar', ['month' => '2026-04']));

        $response->assertOk();

        $weeks = $response->json('eventCalendar.weeks') ?? [];
        $this->assertSame([], $this->extractSpanPlacementsForTitle($weeks, 'Haftalik sessiya'));
        $this->assertContains('Haftalik sessiya', $this->extractPreviewTitlesForDate($weeks, '2026-04-01'));
    }

    public function test_dashboard_keeps_multi_week_visit_visible_in_each_week_of_the_same_month(): void
    {
        $viewVisitsPermission = Permission::findOrCreate('view visits', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewVisitsPermission);

        $country = $this->createCountry('ES', 'ESP');

        Visit::create([
            'title_ru' => 'Nedelniy vizit',
            'title_uz' => 'Haftalik tashrif',
            'country_id' => $country->id,
            'start_date' => '2026-04-01',
            'end_date' => '2026-04-07',
            'status' => 'ongoing',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson(route('dashboard.calendar', ['month' => '2026-04']));

        $response->assertOk();

        $weeks = $response->json('eventCalendar.weeks') ?? [];
        $this->assertSame([], $this->extractSpanPlacementsForTitle($weeks, 'Haftalik tashrif'));
        $this->assertContains('Haftalik tashrif', $this->extractPreviewTitlesForDate($weeks, '2026-04-01'));
    }

    public function test_dashboard_displays_completed_events_and_visits_with_completed_filter(): void
    {
        $viewEventsPermission = Permission::findOrCreate('view events', 'web');
        $viewVisitsPermission = Permission::findOrCreate('view visits', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewEventsPermission);
        $user->givePermissionTo($viewVisitsPermission);

        $country = $this->createCountry('KG', 'KGZ');

        Event::create([
            'title_ru' => 'Zavershenniy event',
            'title_uz' => 'Tugagan tadbir',
            'country_id' => $country->id,
            'start_datetime' => '2026-03-05 09:00:00',
            'end_datetime' => '2026-03-07 18:00:00',
            'format' => 'offline',
            'status' => 'tugatilgan',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Visit::create([
            'title_ru' => 'Zavershenniy vizit',
            'title_uz' => 'Tugatilgan tashrif',
            'country_id' => $country->id,
            'start_date' => '2026-03-06',
            'end_date' => '2026-03-06',
            'status' => 'completed',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee('Tugagan tadbir');
        $response->assertSee('Tugatilgan tashrif');
        $response->assertSee('Tugatilgan');
    }

    public function test_dashboard_displays_partner_contact_birthdays_in_calendar(): void
    {
        $viewPartnerContactsPermission = Permission::findOrCreate('view partner contacts', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewPartnerContactsPermission);

        $country = $this->createCountry('TJ', 'TJK');
        $organization = PartnerOrganization::create([
            'country_id' => $country->id,
            'name_ru' => 'Partner organization',
            'name_uz' => 'Hamkor tashkilot',
            'short_name' => 'HT',
            'status' => 'faol',
        ]);

        PartnerContact::create([
            'partner_organization_id' => $organization->id,
            'full_name_ru' => 'Kontakt birthday',
            'full_name_uz' => 'Sherik kontakti',
            'birthday' => '1990-03-05',
            'position_uz' => 'Maslahatchi',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-03']));

        $response->assertOk();
        $response->assertSee("Tug'ilgan kunlar");
        $response->assertSee('Sherik kontakti');
        $response->assertSee('Hamkor tashkilot');
    }

    public function test_dashboard_renders_birthday_start_markers_alongside_events_on_same_day(): void
    {
        $viewEventsPermission = Permission::findOrCreate('view events', 'web');
        $viewVisitsPermission = Permission::findOrCreate('view visits', 'web');
        $viewPartnerContactsPermission = Permission::findOrCreate('view partner contacts', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo($viewEventsPermission);
        $user->givePermissionTo($viewVisitsPermission);
        $user->givePermissionTo($viewPartnerContactsPermission);

        $country = $this->createCountry('CN', 'CHN');
        $organization = PartnerOrganization::create([
            'country_id' => $country->id,
            'name_ru' => 'Busy organization',
            'name_uz' => 'Band tashkilot',
            'short_name' => 'BT',
            'status' => 'faol',
        ]);

        Event::create([
            'title_ru' => 'Busy event RU',
            'title_uz' => 'Band tadbir',
            'country_id' => $country->id,
            'start_datetime' => '2026-03-05 09:00:00',
            'end_datetime' => '2026-03-07 18:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Visit::create([
            'title_ru' => 'Busy visit RU',
            'title_uz' => 'Band tashrif',
            'country_id' => $country->id,
            'start_date' => '2026-03-05',
            'end_date' => '2026-03-06',
            'status' => 'ongoing',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        PartnerContact::create([
            'partner_organization_id' => $organization->id,
            'full_name_ru' => 'Li Vey',
            'full_name_uz' => 'Li Vey',
            'birthday' => '1990-03-05',
            'position_uz' => 'Maslahatchi',
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson(route('dashboard.calendar', ['month' => '2026-03']));

        $response->assertOk();

        $weeks = $response->json('eventCalendar.weeks') ?? [];
        $titles = $this->extractPreviewTitlesForDate($weeks, '2026-03-05');

        $this->assertContains('Li Vey', $titles);
        $this->assertContains('Band tadbir', $titles);
        $this->assertContains('Band tashrif', $titles);
        $this->assertSame([], $this->extractSegmentsForTitle($weeks, 'Li Vey'));
    }

    private function createCountry(string $iso2, string $iso3): Country
    {
        return Country::create([
            'name_ru' => 'Test country '.$iso2,
            'name_uz' => 'Test davlat '.$iso2,
            'iso2' => $iso2,
            'iso3' => $iso3,
            'cooperation_status' => 'faol',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $weeks
     * @return list<array{start_column: int, span: int}>
     */
    private function extractSpanPlacementsForTitle(array $weeks, string $title): array
    {
        $placements = [];

        foreach ($weeks as $week) {
            foreach (($week['span_lanes'] ?? []) as $lane) {
                foreach ($lane as $segment) {
                    if (($segment['title'] ?? null) !== $title) {
                        continue;
                    }

                    $placements[] = [
                        'start_column' => $segment['start_column'] ?? 0,
                        'span' => $segment['span'] ?? 0,
                    ];
                }
            }
        }

        return $placements;
    }

    /**
     * @param  array<int, array<string, mixed>>  $weeks
     * @return list<string>
     */
    private function extractPreviewTitlesForDate(array $weeks, string $date): array
    {
        foreach ($weeks as $week) {
            foreach (($week['days'] ?? []) as $day) {
                if (($day['date'] ?? '') !== $date) {
                    continue;
                }

                $titles = [];
                foreach (($day['preview_items'] ?? []) as $marker) {
                    $titles[] = (string) ($marker['title'] ?? '');
                }

                return $titles;
            }
        }

        return [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $weeks
     * @return list<array{type: mixed, start_column: mixed, span: mixed}>
     */
    private function extractSegmentsForTitle(array $weeks, string $title): array
    {
        $segments = [];

        foreach ($weeks as $week) {
            foreach (($week['span_lanes'] ?? []) as $lane) {
                foreach ($lane as $segment) {
                    if (($segment['title'] ?? null) !== $title) {
                        continue;
                    }

                    $segments[] = [
                        'type' => $segment['type'] ?? null,
                        'start_column' => $segment['start_column'] ?? null,
                        'span' => $segment['span'] ?? null,
                    ];
                }
            }
        }

        return $segments;
    }
}

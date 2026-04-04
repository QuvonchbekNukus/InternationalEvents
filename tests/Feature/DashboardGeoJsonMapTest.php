<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Document;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardGeoJsonMapTest extends TestCase
{
    use RefreshDatabase;

    private string $geoJsonPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->geoJsonPath = storage_path('framework/testing/dashboard-geojson-'.Str::uuid());
        File::ensureDirectoryExists($this->geoJsonPath);

        config()->set('dashboard.geojson_country_path', $this->geoJsonPath);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->geoJsonPath);

        parent::tearDown();
    }

    public function test_authenticated_users_can_fetch_dashboard_geojson_manifest(): void
    {
        $this->writeGeoJsonFile('UZB.geo.json', 'Uzbekistan');
        $this->writeGeoJsonFile('USA.geo.json', 'United States of America');

        $response = $this
            ->actingAs(User::factory()->create())
            ->getJson(route('dashboard.map.countries.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'code' => 'UZB',
                'name' => 'Uzbekistan',
            ])
            ->assertJsonFragment([
                'code' => 'USA',
                'name' => 'United States of America',
            ]);
    }

    public function test_authenticated_users_can_fetch_single_country_geojson(): void
    {
        $this->writeGeoJsonFile('USA.geo.json', 'United States of America');

        $response = $this
            ->actingAs(User::factory()->create())
            ->getJson(route('dashboard.map.countries.show', ['country' => 'USA']));

        $response->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonPath('metadata.code', 'USA')
            ->assertJsonPath('metadata.name', 'United States of America');
    }

    public function test_dashboard_renders_interactive_geojson_map_component(): void
    {
        $response = $this
            ->actingAs(User::factory()->create())
            ->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('data-dashboard-geojson-map', false)
            ->assertSee(route('dashboard.map.countries.index'), false)
            ->assertSee('Oxirgi yuklangan tadbir', false);
    }

    public function test_dashboard_country_summary_prefers_event_with_latest_document_upload(): void
    {
        $this->writeGeoJsonFile('UZB.geo.json', 'Uzbekistan');

        $user = User::factory()->create();
        Permission::findOrCreate('view events', 'web');
        $user->givePermissionTo('view events');

        $country = Country::query()->create([
            'name_ru' => 'Uzbekistan RU',
            'name_uz' => 'Uzbekistan',
            'iso2' => 'UZ',
            'iso3' => 'UZB',
            'cooperation_status' => 'faol',
        ]);

        $eventOlderRow = Event::query()->create([
            'title_ru' => 'Older row event',
            'title_uz' => 'Older row',
            'country_id' => $country->id,
            'start_datetime' => '2026-01-01 10:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $eventOlderRow->created_at = Carbon::parse('2024-01-01');
        $eventOlderRow->saveQuietly();

        $eventNewerRow = Event::query()->create([
            'title_ru' => 'Newer row event',
            'title_uz' => 'Newer row',
            'country_id' => $country->id,
            'start_datetime' => '2026-06-01 10:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $docOnOlder = Document::query()->create([
            'file_name' => 'fresh.pdf',
            'file_path' => '2026/04/fresh.pdf',
            'file_ext' => 'pdf',
            'file_size' => 10,
            'mime_type' => 'application/pdf',
            'country_id' => $country->id,
            'event_id' => $eventOlderRow->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);
        $docOnOlder->created_at = Carbon::parse('2026-04-01 12:00:00');
        $docOnOlder->saveQuietly();

        $docOnNewer = Document::query()->create([
            'file_name' => 'stale.pdf',
            'file_path' => '2026/03/stale.pdf',
            'file_ext' => 'pdf',
            'file_size' => 10,
            'mime_type' => 'application/pdf',
            'country_id' => $country->id,
            'event_id' => $eventNewerRow->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);
        $docOnNewer->created_at = Carbon::parse('2025-01-01 12:00:00');
        $docOnNewer->saveQuietly();

        $this->actingAs($user)
            ->getJson(route('dashboard.map.countries.summary', ['country' => 'UZB']))
            ->assertOk()
            ->assertJsonPath('event.id', $eventOlderRow->id)
            ->assertJsonPath('event.title', 'Older row');
    }

    public function test_dashboard_country_summary_omits_events_when_user_lacks_event_permissions(): void
    {
        $this->writeGeoJsonFile('UZB.geo.json', 'Uzbekistan');

        $user = User::factory()->create();
        $country = Country::query()->create([
            'name_ru' => 'Uzbekistan RU',
            'name_uz' => 'Uzbekistan',
            'iso2' => 'UZ',
            'iso3' => 'UZB',
            'cooperation_status' => 'faol',
        ]);

        Event::query()->create([
            'title_ru' => 'Secret RU',
            'title_uz' => 'Secret',
            'country_id' => $country->id,
            'start_datetime' => '2026-01-01 10:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson(route('dashboard.map.countries.summary', ['country' => 'UZB']))
            ->assertOk()
            ->assertJsonPath('event', null)
            ->assertJsonPath('visit', null);
    }

    public function test_dashboard_geojson_endpoint_returns_not_found_for_missing_country(): void
    {
        $this->actingAs(User::factory()->create())
            ->getJson(route('dashboard.map.countries.show', ['country' => 'MISSING']))
            ->assertNotFound()
            ->assertJsonPath('message', 'Davlat GeoJSON fayli topilmadi.');
    }

    private function writeGeoJsonFile(string $fileName, string $countryName): void
    {
        File::put($this->geoJsonPath.DIRECTORY_SEPARATOR.$fileName, json_encode([
            'type' => 'FeatureCollection',
            'features' => [
                [
                    'type' => 'Feature',
                    'properties' => [
                        'name' => $countryName,
                    ],
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [
                            [
                                [60.0, 40.0],
                                [61.0, 40.0],
                                [61.0, 41.0],
                                [60.0, 41.0],
                                [60.0, 40.0],
                            ],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR));
    }
}

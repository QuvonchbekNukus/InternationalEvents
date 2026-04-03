<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
            ->assertSee('Test modal oynasi');
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

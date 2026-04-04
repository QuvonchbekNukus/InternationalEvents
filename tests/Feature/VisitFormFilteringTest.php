<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\PartnerOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VisitFormFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_visit_create_form_includes_country_aware_partner_organization_options(): void
    {
        Permission::findOrCreate('create visits', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo('create visits');

        $countryA = Country::query()->create([
            'name_ru' => 'Visit Country A RU',
            'name_uz' => 'Visit Country A',
            'iso2' => 'VA',
            'iso3' => 'VAA',
            'cooperation_status' => 'faol',
        ]);

        $countryB = Country::query()->create([
            'name_ru' => 'Visit Country B RU',
            'name_uz' => 'Visit Country B',
            'iso2' => 'VB',
            'iso3' => 'VBB',
            'cooperation_status' => 'faol',
        ]);

        $organizationA = PartnerOrganization::query()->create([
            'country_id' => $countryA->id,
            'name_ru' => 'Visit Organization A RU',
            'name_uz' => 'Visit Organization A',
            'status' => 'faol',
        ]);

        $organizationB = PartnerOrganization::query()->create([
            'country_id' => $countryB->id,
            'name_ru' => 'Visit Organization B RU',
            'name_uz' => 'Visit Organization B',
            'status' => 'faol',
        ]);

        $this->actingAs($user)
            ->get(route('visits.create'))
            ->assertOk()
            ->assertSee('data-visit-form', false)
            ->assertSee('data-visit-country-select', false)
            ->assertSee('data-visit-organization-select', false)
            ->assertSee('value="'.$organizationA->id.'"', false)
            ->assertSee('data-country-id="'.$countryA->id.'"', false)
            ->assertSee('value="'.$organizationB->id.'"', false)
            ->assertSee('data-country-id="'.$countryB->id.'"', false);
    }
}

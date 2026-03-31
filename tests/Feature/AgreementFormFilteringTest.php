<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\PartnerOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AgreementFormFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_agreement_create_form_includes_country_aware_partner_organization_options(): void
    {
        Permission::findOrCreate('create agreements', 'web');

        $user = User::factory()->create();
        $user->givePermissionTo('create agreements');

        $countryA = Country::query()->create([
            'name_ru' => 'Country A RU',
            'name_uz' => 'Country A',
            'name_cryl' => 'Country A CY',
            'iso2' => 'AA',
            'iso3' => 'AAA',
            'cooperation_status' => 'faol',
        ]);

        $countryB = Country::query()->create([
            'name_ru' => 'Country B RU',
            'name_uz' => 'Country B',
            'name_cryl' => 'Country B CY',
            'iso2' => 'BB',
            'iso3' => 'BBB',
            'cooperation_status' => 'faol',
        ]);

        $organizationA = PartnerOrganization::query()->create([
            'country_id' => $countryA->id,
            'name_ru' => 'Organization A RU',
            'name_uz' => 'Organization A',
            'name_cryl' => 'Organization A CY',
            'status' => 'faol',
        ]);

        $organizationB = PartnerOrganization::query()->create([
            'country_id' => $countryB->id,
            'name_ru' => 'Organization B RU',
            'name_uz' => 'Organization B',
            'name_cryl' => 'Organization B CY',
            'status' => 'faol',
        ]);

        $this->actingAs($user)
            ->get(route('agreements.create'))
            ->assertOk()
            ->assertSee('data-agreement-country-select', false)
            ->assertSee('data-agreement-organization-select', false)
            ->assertSee('value="'.$organizationA->id.'"', false)
            ->assertSee('data-country-id="'.$countryA->id.'"', false)
            ->assertSee('value="'.$organizationB->id.'"', false)
            ->assertSee('data-country-id="'.$countryB->id.'"', false);
    }
}

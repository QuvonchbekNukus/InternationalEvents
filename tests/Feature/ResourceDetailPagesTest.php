<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Country;
use App\Models\Document;
use App\Models\Event;
use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ResourceDetailPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_detail_pages_show_linked_records_for_country_organization_and_contact(): void
    {
        $user = $this->authorizedUser([
            'view countries',
            'view partner organizations',
            'view partner contacts',
            'view agreements',
            'view events',
            'view visits',
            'view documents',
        ]);

        $country = Country::query()->create([
            'name_ru' => 'Test Country RU',
            'name_uz' => 'Test Country',
            'name_cryl' => 'Test Country CY',
            'iso2' => 'TC',
            'iso3' => 'TCT',
            'region_uz' => 'Central Asia',
            'cooperation_status' => 'faol',
        ]);

        $partnerOrganization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => 'Test Organization RU',
            'name_uz' => 'Test Organization',
            'name_cryl' => 'Test Organization CY',
            'short_name' => 'TO',
            'city' => 'Tashkent',
            'website' => 'example.test',
            'status' => 'faol',
        ]);

        $organizationInfoDocument = Document::query()->create([
            'title_uz' => 'Test Organization info fayli',
            'title_ru' => 'Test Organization info file',
            'title_cryl' => 'Test Organization info fayli',
            'file_name' => 'organization-info.pdf',
            'file_path' => 'tests/organization-info.pdf',
            'file_ext' => 'pdf',
            'file_size' => 3072,
            'mime_type' => 'application/pdf',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $partnerOrganization->update([
            'organization_info_document_id' => $organizationInfoDocument->id,
        ]);

        $agreement = Agreement::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'title_ru' => 'Framework Agreement RU',
            'title_uz' => 'Framework Agreement',
            'title_cryl' => 'Framework Agreement CY',
            'short_title_uz' => 'Framework Agreement',
            'status' => 'active',
            'signed_date' => '2026-03-01',
            'created_by' => $user->id,
        ]);

        $event = Event::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'agreement_id' => $agreement->id,
            'title_ru' => 'Annual Forum RU',
            'title_uz' => 'Annual Forum',
            'title_cryl' => 'Annual Forum CY',
            'city' => 'Tashkent',
            'start_datetime' => '2026-04-10 09:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
        ]);

        $visit = Visit::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'title_ru' => 'Delegation Visit RU',
            'title_uz' => 'Delegation Visit',
            'title_cryl' => 'Delegation Visit CY',
            'city' => 'Tashkent',
            'start_date' => '2026-05-12',
            'direction' => 'incoming',
            'status' => 'planned',
            'created_by' => $user->id,
        ]);

        $photoDocument = Document::query()->create([
            'title_uz' => 'Alice Contact fotosurati',
            'title_ru' => 'Alice Contact Photo',
            'title_cryl' => 'Alice Contact Photo CY',
            'file_name' => 'profile-photo.png',
            'file_path' => 'tests/profile-photo.png',
            'file_ext' => 'png',
            'file_size' => 2048,
            'mime_type' => 'image/png',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $cvDocument = Document::query()->create([
            'title_uz' => 'Alice Contact CV',
            'title_ru' => 'Alice Contact CV RU',
            'title_cryl' => 'Alice Contact CV CY',
            'file_name' => 'alice-cv.pdf',
            'file_path' => 'tests/alice-cv.pdf',
            'file_ext' => 'pdf',
            'file_size' => 4096,
            'mime_type' => 'application/pdf',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $partnerContact = PartnerContact::query()->create([
            'partner_organization_id' => $partnerOrganization->id,
            'full_name_ru' => 'Alice Contact RU',
            'full_name_uz' => 'Alice Contact',
            'full_name_cryl' => 'Alice Contact CY',
            'birthday' => '1990-01-15',
            'position_ru' => 'Coordinator RU',
            'position_uz' => 'Coordinator',
            'position_cryl' => 'Coordinator CY',
            'email' => 'alice@example.test',
            'phone' => '+998901234567',
            'photo' => $photoDocument->id,
            'cv' => $cvDocument->id,
            'is_primary' => true,
        ]);

        Document::query()->create([
            'title_uz' => 'Overview File',
            'title_ru' => 'Overview File RU',
            'title_cryl' => 'Overview File CY',
            'file_name' => 'overview.docx',
            'file_path' => 'tests/overview.docx',
            'file_ext' => 'docx',
            'file_size' => 5120,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'agreement_id' => $agreement->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        Document::query()->create([
            'title_uz' => 'Event Attachment',
            'title_ru' => 'Event Attachment RU',
            'title_cryl' => 'Event Attachment CY',
            'file_name' => 'event-attachment.pdf',
            'file_path' => 'tests/event-attachment.pdf',
            'file_ext' => 'pdf',
            'file_size' => 6144,
            'mime_type' => 'application/pdf',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'agreement_id' => $agreement->id,
            'event_id' => $event->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $this->actingAs($user)
            ->get(route('countries.show', $country))
            ->assertOk()
            ->assertSee('Test Organization')
            ->assertSee('Framework Agreement')
            ->assertSee('Annual Forum')
            ->assertSee('Delegation Visit')
            ->assertSee('Overview File');

        $this->actingAs($user)
            ->get(route('agreements.show', $agreement))
            ->assertOk()
            ->assertSee('Framework Agreement')
            ->assertSee('overview.docx');

        $this->actingAs($user)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Annual Forum')
            ->assertSee('event-attachment.pdf');

        $this->actingAs($user)
            ->get(route('partner-organizations.show', $partnerOrganization))
            ->assertOk()
            ->assertSee('Alice Contact')
            ->assertSee('Framework Agreement')
            ->assertSee('Annual Forum')
            ->assertSee('Delegation Visit')
            ->assertSee('organization-info.pdf')
            ->assertSee('Foto')
            ->assertSee('CV');

        $this->actingAs($user)
            ->get(route('partner-contacts.show', $partnerContact))
            ->assertOk()
            ->assertSee('Alice Contact')
            ->assertSee('Test Organization')
            ->assertSee('profile-photo.png')
            ->assertSee('alice-cv.pdf')
            ->assertSee('/documents/tests/profile-photo.png')
            ->assertSee('/documents/tests/alice-cv.pdf');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function authorizedUser(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        return $user;
    }
}

<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Document;
use App\Models\PartnerOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PartnerOrganizationAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_partner_organization_store_creates_linked_info_document(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view partner organizations',
            'create partner organizations',
        ]);

        $country = $this->createCountry();

        $response = $this->actingAs($user)->post(route('partner-organizations.store'), [
            'country_id' => $country->id,
            'name_ru' => 'Test Organization RU',
            'name_uz' => 'Test Organization',
            'name_cryl' => 'Test Organization CY',
            'short_name' => 'TO',
            'city' => 'Tashkent',
            'website' => 'example.test',
            'status' => 'faol',
            'notes' => 'Test notes',
            'organization_info_file' => UploadedFile::fake()->create('org-info.pdf', 140, 'application/pdf'),
        ]);

        $response->assertRedirect(route('partner-organizations.index'));

        $partnerOrganization = PartnerOrganization::query()
            ->with('organizationInfoDocument')
            ->firstOrFail();

        $this->assertNotNull($partnerOrganization->organization_info_document_id);
        $this->assertSame($user->id, $partnerOrganization->organizationInfoDocument?->uploaded_by);
        $this->assertSame($country->id, $partnerOrganization->organizationInfoDocument?->country_id);
        $this->assertSame($partnerOrganization->id, $partnerOrganization->organizationInfoDocument?->partner_organization_id);
        $this->assertSame('org-info.pdf', $partnerOrganization->organizationInfoDocument?->file_name);
        Storage::disk('documents')->assertExists($partnerOrganization->organizationInfoDocument->file_path);
    }

    public function test_partner_organization_update_replaces_existing_info_document_without_changing_document_id(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view partner organizations',
            'edit partner organizations',
        ]);

        $country = $this->createCountry();

        Storage::disk('documents')->put('2026/03/org-info-old.pdf', 'old-info');

        $partnerOrganization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => 'Stored Organization RU',
            'name_uz' => 'Stored Organization',
            'name_cryl' => 'Stored Organization CY',
            'status' => 'faol',
        ]);

        $document = Document::query()->create([
            'title_uz' => 'Stored Organization info fayli',
            'title_ru' => 'Stored Organization info file',
            'title_cryl' => 'Stored Organization info fayli',
            'file_name' => 'org-info-old.pdf',
            'file_path' => '2026/03/org-info-old.pdf',
            'file_ext' => 'pdf',
            'file_size' => 8,
            'mime_type' => 'application/pdf',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $partnerOrganization->update([
            'organization_info_document_id' => $document->id,
        ]);

        $response = $this->actingAs($user)->post(route('partner-organizations.update', $partnerOrganization), [
            '_method' => 'PUT',
            'country_id' => $country->id,
            'name_ru' => 'Updated Organization RU',
            'name_uz' => 'Updated Organization',
            'name_cryl' => 'Updated Organization CY',
            'short_name' => 'UO',
            'city' => 'Samarkand',
            'website' => 'updated.example.test',
            'status' => 'faol',
            'notes' => 'Updated notes',
            'organization_info_file' => UploadedFile::fake()->create('org-info-new.docx', 220, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $response->assertRedirect(route('partner-organizations.index'));

        $partnerOrganization->refresh();
        $document->refresh();

        $this->assertSame($document->id, $partnerOrganization->organization_info_document_id);
        $this->assertSame('org-info-new.docx', $document->file_name);
        $this->assertSame('Updated Organization info fayli', $document->title_uz);
        Storage::disk('documents')->assertMissing('2026/03/org-info-old.pdf');
        Storage::disk('documents')->assertExists($document->file_path);
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/.+$/', $document->file_path);
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

    private function createCountry(): Country
    {
        return Country::query()->create([
            'name_ru' => 'Test Country RU',
            'name_uz' => 'Test Country',
            'name_cryl' => 'Test Country CY',
            'iso2' => 'TC',
            'iso3' => 'TCT',
            'cooperation_status' => 'faol',
        ]);
    }
}

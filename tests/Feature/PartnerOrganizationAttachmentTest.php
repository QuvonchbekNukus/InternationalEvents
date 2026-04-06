<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Document;
use App\Models\PartnerOrganization;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'short_name' => 'TO',
            'city' => 'Tashkent',
            'website' => 'example.test',
            'status' => 'faol',
            'notes' => 'Test notes',
            'partnership_history' => "Hamkorlik 2024-yilda boshlangan.\nQo'shma uchrashuv o'tkazilgan.",
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
        $this->assertSame("Hamkorlik 2024-yilda boshlangan.\nQo'shma uchrashuv o'tkazilgan.", $partnerOrganization->partnership_history);
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
            'status' => 'faol',
        ]);

        $document = Document::query()->create([
            'title_uz' => 'Stored Organization info fayli',
            'title_ru' => 'Stored Organization info file',
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
            'short_name' => 'UO',
            'city' => 'Samarkand',
            'website' => 'updated.example.test',
            'status' => 'faol',
            'notes' => 'Updated notes',
            'partnership_history' => "Hamkorlik tarixi yangilandi.\nYangi memorandum loyihasi tayyorlandi.",
            'organization_info_file' => UploadedFile::fake()->create('org-info-new.docx', 220, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $response->assertRedirect(route('partner-organizations.index'));

        $partnerOrganization->refresh();
        $document->refresh();

        $this->assertSame($document->id, $partnerOrganization->organization_info_document_id);
        $this->assertSame('org-info-new.docx', $document->file_name);
        $this->assertSame('Updated Organization info fayli', $document->title_uz);
        $this->assertSame("Hamkorlik tarixi yangilandi.\nYangi memorandum loyihasi tayyorlandi.", $partnerOrganization->partnership_history);
        Storage::disk('documents')->assertMissing('2026/03/org-info-old.pdf');
        Storage::disk('documents')->assertExists($document->file_path);
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/.+$/', $document->file_path);
    }

    public function test_partner_organization_edit_can_delete_existing_info_file_and_storage_file(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view partner organizations',
            'edit partner organizations',
        ]);

        $country = $this->createCountry();

        $partnerOrganization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => 'Delete Organization RU',
            'name_uz' => 'Delete Organization',
            'status' => 'faol',
        ]);

        Storage::disk('documents')->put('2026/04/organization-delete.pdf', 'delete-me');

        $document = Document::query()->create([
            'title_uz' => 'Delete Organization info fayli',
            'title_ru' => 'Delete Organization info file',
            'file_name' => 'organization-delete.pdf',
            'file_path' => '2026/04/organization-delete.pdf',
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

        $this->actingAs($user)
            ->delete(route('partner-organizations.organization-info.destroy', $partnerOrganization))
            ->assertRedirect(route('partner-organizations.edit', $partnerOrganization));

        $partnerOrganization->refresh();

        $this->assertNull($partnerOrganization->organization_info_document_id);
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        Storage::disk('documents')->assertMissing('2026/04/organization-delete.pdf');
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
            'iso2' => 'TC',
            'iso3' => 'TCT',
            'cooperation_status' => 'faol',
        ]);
    }
}

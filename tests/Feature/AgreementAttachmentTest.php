<?php

namespace Tests\Feature;

use App\Models\Agreement;
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

class AgreementAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_agreement_store_creates_linked_documents_from_uploaded_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view agreements',
            'create agreements',
        ]);

        [$country, $partnerOrganization] = $this->createCountryAndOrganization('A');

        $response = $this->actingAs($user)->post(route('agreements.store'), [
            'agreement_number' => 'AG-001',
            'title_ru' => 'Agreement RU',
            'title_uz' => 'Agreement UZ',
            'title_cryl' => 'Agreement CY',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'status' => 'draft',
            'agreement_files' => [
                UploadedFile::fake()->create('agreement.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->create('terms.docx', 140, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ],
        ]);

        $response->assertRedirect(route('agreements.index'));

        $agreement = Agreement::query()->with('documents')->firstOrFail();

        $this->assertCount(2, $agreement->documents);
        $this->assertEqualsCanonicalizing(
            ['agreement.pdf', 'terms.docx'],
            $agreement->documents->pluck('file_name')->all()
        );

        foreach ($agreement->documents as $document) {
            $this->assertSame($agreement->id, $document->agreement_id);
            $this->assertSame($country->id, $document->country_id);
            $this->assertSame($partnerOrganization->id, $document->partner_organization_id);
            $this->assertSame($user->id, $document->uploaded_by);
            Storage::disk('documents')->assertExists($document->file_path);
        }
    }

    public function test_agreement_update_replaces_existing_documents_and_removes_old_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view agreements',
            'edit agreements',
        ]);

        [$countryA, $partnerOrganizationA] = $this->createCountryAndOrganization('A');
        [$countryB, $partnerOrganizationB] = $this->createCountryAndOrganization('B');

        $agreement = Agreement::query()->create([
            'agreement_number' => 'AG-002',
            'title_ru' => 'Old Agreement RU',
            'title_uz' => 'Old Agreement UZ',
            'title_cryl' => 'Old Agreement CY',
            'country_id' => $countryA->id,
            'partner_organization_id' => $partnerOrganizationA->id,
            'status' => 'draft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Storage::disk('documents')->put('2026/03/existing-agreement-file.pdf', 'old-file');

        $existingDocument = Document::query()->create([
            'title_ru' => null,
            'title_uz' => null,
            'title_cryl' => null,
            'document_number' => null,
            'document_type_id' => null,
            'file_name' => 'existing-agreement-file.pdf',
            'file_path' => '2026/03/existing-agreement-file.pdf',
            'file_ext' => 'pdf',
            'file_size' => 8,
            'mime_type' => 'application/pdf',
            'country_id' => $countryA->id,
            'partner_organization_id' => $partnerOrganizationA->id,
            'agreement_id' => $agreement->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $response = $this->actingAs($user)->put(route('agreements.update', $agreement), [
            'agreement_number' => 'AG-002',
            'title_ru' => 'Updated Agreement RU',
            'title_uz' => 'Updated Agreement UZ',
            'title_cryl' => 'Updated Agreement CY',
            'country_id' => $countryB->id,
            'partner_organization_id' => $partnerOrganizationB->id,
            'status' => 'active',
            'agreement_files' => [
                UploadedFile::fake()->create('new-appendix.doc', 90, 'application/msword'),
            ],
        ]);

        $response->assertRedirect(route('agreements.index'));

        $agreement->refresh();
        $agreement->load('documents');

        $this->assertCount(1, $agreement->documents);
        $this->assertDatabaseMissing('documents', ['id' => $existingDocument->id]);

        $newDocument = $agreement->documents->firstWhere('file_name', 'new-appendix.doc');
        $this->assertSame($countryB->id, $newDocument->country_id);
        $this->assertSame($partnerOrganizationB->id, $newDocument->partner_organization_id);
        Storage::disk('documents')->assertMissing($existingDocument->file_path);
        Storage::disk('documents')->assertExists($newDocument->file_path);
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

    /**
     * @return array{Country, PartnerOrganization}
     */
    private function createCountryAndOrganization(string $suffix): array
    {
        $country = Country::query()->create([
            'name_ru' => "Country {$suffix} RU",
            'name_uz' => "Country {$suffix}",
            'name_cryl' => "Country {$suffix} CY",
            'iso2' => "C{$suffix}",
            'iso3' => "CT{$suffix}",
            'cooperation_status' => 'faol',
        ]);

        $partnerOrganization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => "Organization {$suffix} RU",
            'name_uz' => "Organization {$suffix}",
            'name_cryl' => "Organization {$suffix} CY",
            'status' => 'faol',
        ]);

        return [$country, $partnerOrganization];
    }
}

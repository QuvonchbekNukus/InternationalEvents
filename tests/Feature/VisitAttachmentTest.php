<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Document;
use App\Models\PartnerOrganization;
use App\Models\Permission;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class VisitAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_visit_store_creates_linked_documents_and_show_separates_images_from_other_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view visits',
            'create visits',
        ]);

        [$country, $partnerOrganization] = $this->createCountryAndOrganization('A');

        $response = $this->actingAs($user)->post(route('visits.store'), [
            'title_ru' => 'Visit RU',
            'title_uz' => 'Visit UZ',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'start_date' => '2026-04-15',
            'status' => 'planned',
            'visit_files' => [
                UploadedFile::fake()->create('visit-photo.jpg', 120, 'image/jpeg'),
                UploadedFile::fake()->create('briefing.pdf', 140, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('visits.index'));

        $visit = Visit::query()->with('documents')->firstOrFail();

        $this->assertCount(2, $visit->documents);
        $this->assertEqualsCanonicalizing(
            ['visit-photo.jpg', 'briefing.pdf'],
            $visit->documents->pluck('file_name')->all()
        );

        foreach ($visit->documents as $document) {
            $this->assertSame($visit->id, $document->visit_id);
            $this->assertSame($country->id, $document->country_id);
            $this->assertSame($partnerOrganization->id, $document->partner_organization_id);
            $this->assertSame($user->id, $document->uploaded_by);
            Storage::disk('documents')->assertExists($document->file_path);
        }

        $this->actingAs($user)
            ->get(route('visits.show', $visit))
            ->assertOk()
            ->assertSeeText('Rasm previewlari')
            ->assertSeeText('Boshqa biriktirmalar')
            ->assertSeeText('visit-photo.jpg')
            ->assertSeeText('briefing.pdf');
    }

    public function test_visit_update_replaces_existing_documents_and_removes_old_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view visits',
            'edit visits',
        ]);

        [$countryA, $partnerOrganizationA] = $this->createCountryAndOrganization('B');
        [$countryB, $partnerOrganizationB] = $this->createCountryAndOrganization('C');

        $visit = Visit::query()->create([
            'title_ru' => 'Old Visit RU',
            'title_uz' => 'Old Visit UZ',
            'country_id' => $countryA->id,
            'partner_organization_id' => $partnerOrganizationA->id,
            'start_date' => '2026-04-16',
            'status' => 'planned',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Storage::disk('documents')->put('2026/04/existing-visit-file.pdf', 'old-file');

        $existingDocument = Document::query()->create([
            'title_ru' => null,
            'title_uz' => null,
            'document_number' => null,
            'document_type_id' => null,
            'file_name' => 'existing-visit-file.pdf',
            'file_path' => '2026/04/existing-visit-file.pdf',
            'file_ext' => 'pdf',
            'file_size' => 8,
            'mime_type' => 'application/pdf',
            'country_id' => $countryA->id,
            'partner_organization_id' => $partnerOrganizationA->id,
            'visit_id' => $visit->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $response = $this->actingAs($user)->put(route('visits.update', $visit), [
            'title_ru' => 'Updated Visit RU',
            'title_uz' => 'Updated Visit UZ',
            'country_id' => $countryB->id,
            'partner_organization_id' => $partnerOrganizationB->id,
            'start_date' => '2026-04-17',
            'status' => 'ongoing',
            'visit_files' => [
                UploadedFile::fake()->create('updated-visit-photo.png', 95, 'image/png'),
            ],
        ]);

        $response->assertRedirect(route('visits.index'));

        $visit->refresh();
        $visit->load('documents');

        $this->assertCount(1, $visit->documents);
        $this->assertDatabaseMissing('documents', ['id' => $existingDocument->id]);

        $newDocument = $visit->documents->firstWhere('file_name', 'updated-visit-photo.png');
        $this->assertNotNull($newDocument);
        $this->assertSame($countryB->id, $newDocument->country_id);
        $this->assertSame($partnerOrganizationB->id, $newDocument->partner_organization_id);
        $this->assertSame($visit->id, $newDocument->visit_id);
        Storage::disk('documents')->assertMissing($existingDocument->file_path);
        Storage::disk('documents')->assertExists($newDocument->file_path);
    }

    public function test_visit_edit_can_delete_existing_attachment_and_storage_file(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view visits',
            'edit visits',
        ]);

        [$country, $partnerOrganization] = $this->createCountryAndOrganization('D');

        $visit = Visit::query()->create([
            'title_ru' => 'Delete Visit RU',
            'title_uz' => 'Delete Visit UZ',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'start_date' => '2026-04-18',
            'status' => 'planned',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Storage::disk('documents')->put('2026/04/visit-delete.docx', 'delete-me');

        $document = Document::query()->create([
            'title_ru' => null,
            'title_uz' => null,
            'document_number' => null,
            'document_type_id' => null,
            'file_name' => 'visit-delete.docx',
            'file_path' => '2026/04/visit-delete.docx',
            'file_ext' => 'docx',
            'file_size' => 10,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'visit_id' => $visit->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('visits.attachments.destroy', [$visit, $document]))
            ->assertRedirect(route('visits.edit', $visit));

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        Storage::disk('documents')->assertMissing('2026/04/visit-delete.docx');
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
            'name_ru' => "Visit Country {$suffix} RU",
            'name_uz' => "Visit Country {$suffix}",
            'iso2' => "V{$suffix}",
            'iso3' => "VIS{$suffix}",
            'cooperation_status' => 'faol',
        ]);

        $partnerOrganization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => "Visit Organization {$suffix} RU",
            'name_uz' => "Visit Organization {$suffix}",
            'status' => 'faol',
        ]);

        return [$country, $partnerOrganization];
    }
}

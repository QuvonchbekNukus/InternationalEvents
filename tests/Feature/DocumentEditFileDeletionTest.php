<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Document;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DocumentEditFileDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_document_edit_can_delete_owned_document_file_and_record(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser(['view own documents', 'edit own documents']);
        $country = $this->createCountry();

        Storage::disk('documents')->put('2026/04/document-delete.pdf', 'delete-me');

        $document = Document::query()->create([
            'title_uz' => 'Delete me',
            'title_ru' => 'Delete me',
            'document_number' => 'DOC-DEL-001',
            'document_type_id' => null,
            'country_id' => $country->id,
            'partner_organization_id' => null,
            'agreement_id' => null,
            'visit_id' => null,
            'event_id' => null,
            'file_name' => 'document-delete.pdf',
            'file_path' => '2026/04/document-delete.pdf',
            'file_ext' => 'pdf',
            'file_size' => 9,
            'mime_type' => 'application/pdf',
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('documents.file.destroy', $document))
            ->assertRedirect(route('documents.index'));

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        Storage::disk('documents')->assertMissing('2026/04/document-delete.pdf');
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
            'name_ru' => 'Delete Country RU',
            'name_uz' => 'Delete Country',
            'iso2' => 'DD',
            'iso3' => 'DDE',
            'cooperation_status' => 'faol',
        ]);
    }
}

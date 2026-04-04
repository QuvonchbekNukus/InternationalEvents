<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Document;
use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PartnerContactAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_partner_contact_store_creates_linked_photo_and_cv_documents(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view partner contacts',
            'create partner contacts',
        ]);

        $partnerOrganization = $this->createPartnerOrganization();

        $response = $this->actingAs($user)->post(route('partner-contacts.store'), [
            'partner_organization_id' => $partnerOrganization->id,
            'full_name_ru' => 'Тестовый Контакт',
            'full_name_uz' => 'Test Kontakt',
            'birthday' => '1990-01-15',
            'position_ru' => 'Советник',
            'position_uz' => 'Maslahatchi',
            'email' => 'contact@example.test',
            'phone' => '+998901234567',
            'description' => 'Test izoh',
            'is_primary' => '1',
            'photo_file' => $this->fakeImageUpload('photo.png'),
            'cv_file' => UploadedFile::fake()->create('resume.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect(route('partner-contacts.index'));

        $partnerContact = PartnerContact::query()
            ->with(['photoDocument', 'cvDocument'])
            ->firstOrFail();

        $this->assertNotNull($partnerContact->photo);
        $this->assertNotNull($partnerContact->cv);
        $this->assertSame($partnerOrganization->id, $partnerContact->partner_organization_id);
        $this->assertSame($user->id, $partnerContact->photoDocument?->uploaded_by);
        $this->assertSame($user->id, $partnerContact->cvDocument?->uploaded_by);
        $this->assertSame($partnerOrganization->country_id, $partnerContact->photoDocument?->country_id);
        $this->assertSame($partnerOrganization->country_id, $partnerContact->cvDocument?->country_id);
        $this->assertSame($partnerOrganization->id, $partnerContact->photoDocument?->partner_organization_id);
        $this->assertSame($partnerOrganization->id, $partnerContact->cvDocument?->partner_organization_id);

        Storage::disk('documents')->assertExists($partnerContact->photoDocument->file_path);
        Storage::disk('documents')->assertExists($partnerContact->cvDocument->file_path);

        $this->actingAs($user)
            ->get(route('partner-contacts.attachments.preview', ['partnerContact' => $partnerContact, 'type' => 'photo']))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('partner-contacts.attachments.preview', ['partnerContact' => $partnerContact, 'type' => 'cv']))
            ->assertOk();
    }

    public function test_partner_contact_update_replaces_existing_attachment_files_without_changing_document_ids(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view partner contacts',
            'edit partner contacts',
        ]);

        $partnerOrganization = $this->createPartnerOrganization();

        Storage::disk('documents')->put('2026/03/photo-old.jpg', 'old-photo');
        Storage::disk('documents')->put('2026/03/cv-old.pdf', 'old-cv');

        $photoDocument = Document::query()->create([
            'title_uz' => 'Eski foto',
            'title_ru' => 'Старое фото',
            'file_name' => 'photo-old.jpg',
            'file_path' => '2026/03/photo-old.jpg',
            'file_ext' => 'jpg',
            'file_size' => 9,
            'mime_type' => 'image/jpeg',
            'country_id' => $partnerOrganization->country_id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $cvDocument = Document::query()->create([
            'title_uz' => 'Eski CV',
            'title_ru' => 'Старое CV',
            'file_name' => 'cv-old.pdf',
            'file_path' => '2026/03/cv-old.pdf',
            'file_ext' => 'pdf',
            'file_size' => 6,
            'mime_type' => 'application/pdf',
            'country_id' => $partnerOrganization->country_id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $partnerContact = PartnerContact::query()->create([
            'partner_organization_id' => $partnerOrganization->id,
            'full_name_ru' => 'Старый Контакт',
            'full_name_uz' => 'Eski Kontakt',
            'photo' => $photoDocument->id,
            'cv' => $cvDocument->id,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user)->post(route('partner-contacts.update', $partnerContact), [
            '_method' => 'PUT',
            'partner_organization_id' => $partnerOrganization->id,
            'full_name_ru' => 'Yangilangan Kontakt RU',
            'full_name_uz' => 'Yangilangan Kontakt',
            'birthday' => '1992-05-10',
            'position_ru' => 'Советник',
            'position_uz' => 'Maslahatchi',
            'email' => 'updated@example.test',
            'phone' => '+998909999999',
            'description' => 'Yangilangan izoh',
            'is_primary' => '0',
            'photo_file' => $this->fakeImageUpload('photo-new.png'),
            'cv_file' => UploadedFile::fake()->create('cv-new.docx', 220, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
        ]);

        $response->assertRedirect(route('partner-contacts.index'));

        $partnerContact->refresh();
        $photoDocument->refresh();
        $cvDocument->refresh();

        $this->assertSame($photoDocument->id, $partnerContact->photo);
        $this->assertSame($cvDocument->id, $partnerContact->cv);
        $this->assertSame('photo-new.png', $photoDocument->file_name);
        $this->assertSame('cv-new.docx', $cvDocument->file_name);
        $this->assertSame('Yangilangan Kontakt fotosurati', $photoDocument->title_uz);
        $this->assertSame('Yangilangan Kontakt CV', $cvDocument->title_uz);

        Storage::disk('documents')->assertMissing('2026/03/photo-old.jpg');
        Storage::disk('documents')->assertMissing('2026/03/cv-old.pdf');
        Storage::disk('documents')->assertExists($photoDocument->file_path);
        Storage::disk('documents')->assertExists($cvDocument->file_path);
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/.+$/', $photoDocument->file_path);
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{2}\/.+$/', $cvDocument->file_path);
    }

    public function test_partner_contact_edit_can_delete_selected_attachment_and_storage_file(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view partner contacts',
            'edit partner contacts',
        ]);

        $partnerOrganization = $this->createPartnerOrganization();

        Storage::disk('documents')->put('2026/04/contact-photo-delete.jpg', 'delete-photo');
        Storage::disk('documents')->put('2026/04/contact-cv-keep.pdf', 'keep-cv');

        $photoDocument = Document::query()->create([
            'title_uz' => 'Delete foto',
            'title_ru' => 'Delete photo',
            'file_name' => 'contact-photo-delete.jpg',
            'file_path' => '2026/04/contact-photo-delete.jpg',
            'file_ext' => 'jpg',
            'file_size' => 5,
            'mime_type' => 'image/jpeg',
            'country_id' => $partnerOrganization->country_id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $cvDocument = Document::query()->create([
            'title_uz' => 'Keep CV',
            'title_ru' => 'Keep CV',
            'file_name' => 'contact-cv-keep.pdf',
            'file_path' => '2026/04/contact-cv-keep.pdf',
            'file_ext' => 'pdf',
            'file_size' => 7,
            'mime_type' => 'application/pdf',
            'country_id' => $partnerOrganization->country_id,
            'partner_organization_id' => $partnerOrganization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $partnerContact = PartnerContact::query()->create([
            'partner_organization_id' => $partnerOrganization->id,
            'full_name_ru' => 'Delete Contact RU',
            'full_name_uz' => 'Delete Contact',
            'photo' => $photoDocument->id,
            'cv' => $cvDocument->id,
            'is_primary' => false,
        ]);

        $this->actingAs($user)
            ->delete(route('partner-contacts.attachments.destroy', ['partnerContact' => $partnerContact, 'type' => 'photo']))
            ->assertRedirect(route('partner-contacts.edit', $partnerContact));

        $partnerContact->refresh();

        $this->assertNull($partnerContact->photo);
        $this->assertSame($cvDocument->id, $partnerContact->cv);
        $this->assertDatabaseMissing('documents', ['id' => $photoDocument->id]);
        $this->assertDatabaseHas('documents', ['id' => $cvDocument->id]);
        Storage::disk('documents')->assertMissing('2026/04/contact-photo-delete.jpg');
        Storage::disk('documents')->assertExists('2026/04/contact-cv-keep.pdf');
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

    private function createPartnerOrganization(): PartnerOrganization
    {
        $country = Country::query()->create([
            'name_ru' => 'Тестовая страна',
            'name_uz' => 'Test davlat',
            'iso2' => 'TS',
            'iso3' => 'TST',
            'cooperation_status' => 'faol',
        ]);

        return PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => 'Тестовая организация',
            'name_uz' => 'Test tashkilot',
            'status' => 'faol',
        ]);
    }

    private function fakeImageUpload(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'pct');

        file_put_contents(
            $path,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aK3sAAAAASUVORK5CYII=')
        );

        return new UploadedFile(
            $path,
            $name,
            'image/png',
            null,
            true
        );
    }
}

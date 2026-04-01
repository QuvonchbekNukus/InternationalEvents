<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Country;
use App\Models\Document;
use App\Models\Event;
use App\Models\PartnerOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EventAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_event_store_creates_linked_documents_from_uploaded_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view events',
            'create events',
        ]);

        [$country, $partnerOrganization, $agreement] = $this->createCountryOrganizationAndAgreement($user, 'A');

        $response = $this->actingAs($user)->post(route('events.store'), [
            'title_ru' => 'Event RU',
            'title_uz' => 'Event UZ',
            'title_cryl' => 'Event CY',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'agreement_id' => $agreement->id,
            'start_datetime' => '2026-04-10 09:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'event_files' => [
                UploadedFile::fake()->create('agenda.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->create('participants.docx', 140, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ],
        ]);

        $response->assertRedirect(route('events.index'));

        $event = Event::query()->with('documents')->firstOrFail();

        $this->assertCount(2, $event->documents);
        $this->assertEqualsCanonicalizing(
            ['agenda.pdf', 'participants.docx'],
            $event->documents->pluck('file_name')->all()
        );

        foreach ($event->documents as $document) {
            $this->assertSame($event->id, $document->event_id);
            $this->assertSame($country->id, $document->country_id);
            $this->assertSame($partnerOrganization->id, $document->partner_organization_id);
            $this->assertSame($agreement->id, $document->agreement_id);
            $this->assertSame($user->id, $document->uploaded_by);
            Storage::disk('documents')->assertExists($document->file_path);
        }
    }

    public function test_event_update_replaces_existing_documents_and_removes_old_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view events',
            'edit events',
        ]);

        [$countryA, $partnerOrganizationA, $agreementA] = $this->createCountryOrganizationAndAgreement($user, 'A');
        [$countryB, $partnerOrganizationB, $agreementB] = $this->createCountryOrganizationAndAgreement($user, 'B');

        $event = Event::query()->create([
            'title_ru' => 'Old Event RU',
            'title_uz' => 'Old Event UZ',
            'title_cryl' => 'Old Event CY',
            'country_id' => $countryA->id,
            'partner_organization_id' => $partnerOrganizationA->id,
            'agreement_id' => $agreementA->id,
            'start_datetime' => '2026-04-10 09:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Storage::disk('documents')->put('2026/03/existing-event-file.pdf', 'old-file');

        $existingDocument = Document::query()->create([
            'title_ru' => null,
            'title_uz' => null,
            'title_cryl' => null,
            'document_number' => null,
            'document_type_id' => null,
            'file_name' => 'existing-event-file.pdf',
            'file_path' => '2026/03/existing-event-file.pdf',
            'file_ext' => 'pdf',
            'file_size' => 8,
            'mime_type' => 'application/pdf',
            'country_id' => $countryA->id,
            'partner_organization_id' => $partnerOrganizationA->id,
            'agreement_id' => $agreementA->id,
            'event_id' => $event->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        $response = $this->actingAs($user)->put(route('events.update', $event), [
            'title_ru' => 'Updated Event RU',
            'title_uz' => 'Updated Event UZ',
            'title_cryl' => 'Updated Event CY',
            'country_id' => $countryB->id,
            'partner_organization_id' => $partnerOrganizationB->id,
            'agreement_id' => $agreementB->id,
            'start_datetime' => '2026-04-11 10:00:00',
            'format' => 'online',
            'status' => 'hozirda',
            'event_files' => [
                UploadedFile::fake()->create('new-program.doc', 90, 'application/msword'),
            ],
        ]);

        $response->assertRedirect(route('events.index'));

        $event->refresh();
        $event->load('documents');

        $this->assertCount(1, $event->documents);
        $this->assertDatabaseMissing('documents', ['id' => $existingDocument->id]);

        $newDocument = $event->documents->firstWhere('file_name', 'new-program.doc');
        $this->assertSame($countryB->id, $newDocument->country_id);
        $this->assertSame($partnerOrganizationB->id, $newDocument->partner_organization_id);
        $this->assertSame($agreementB->id, $newDocument->agreement_id);
        Storage::disk('documents')->assertMissing($existingDocument->file_path);
        Storage::disk('documents')->assertExists($newDocument->file_path);
    }

    public function test_event_edit_can_delete_existing_attachment_and_storage_file(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser([
            'view events',
            'edit events',
        ]);

        [$country, $partnerOrganization, $agreement] = $this->createCountryOrganizationAndAgreement($user, 'C');

        $event = Event::query()->create([
            'title_ru' => 'Delete Event RU',
            'title_uz' => 'Delete Event UZ',
            'title_cryl' => 'Delete Event CY',
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'agreement_id' => $agreement->id,
            'start_datetime' => '2026-05-12 10:00:00',
            'format' => 'offline',
            'status' => 'rejada',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        Storage::disk('documents')->put('2026/04/event-delete.pdf', 'delete-me');

        $document = Document::query()->create([
            'title_ru' => null,
            'title_uz' => null,
            'title_cryl' => null,
            'document_number' => null,
            'document_type_id' => null,
            'file_name' => 'event-delete.pdf',
            'file_path' => '2026/04/event-delete.pdf',
            'file_ext' => 'pdf',
            'file_size' => 9,
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
            ->delete(route('events.attachments.destroy', [$event, $document]))
            ->assertRedirect(route('events.edit', $event));

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
        Storage::disk('documents')->assertMissing('2026/04/event-delete.pdf');
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
     * @return array{Country, PartnerOrganization, Agreement}
     */
    private function createCountryOrganizationAndAgreement(User $user, string $suffix): array
    {
        $country = Country::query()->create([
            'name_ru' => "Country {$suffix} RU",
            'name_uz' => "Country {$suffix}",
            'name_cryl' => "Country {$suffix} CY",
            'iso2' => "E{$suffix}",
            'iso3' => "EV{$suffix}",
            'cooperation_status' => 'faol',
        ]);

        $partnerOrganization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => "Organization {$suffix} RU",
            'name_uz' => "Organization {$suffix}",
            'name_cryl' => "Organization {$suffix} CY",
            'status' => 'faol',
        ]);

        $agreement = Agreement::query()->create([
            'title_ru' => "Agreement {$suffix} RU",
            'title_uz' => "Agreement {$suffix}",
            'title_cryl' => "Agreement {$suffix} CY",
            'country_id' => $country->id,
            'partner_organization_id' => $partnerOrganization->id,
            'status' => 'draft',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return [$country, $partnerOrganization, $agreement];
    }
}

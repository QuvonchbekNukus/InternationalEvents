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
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DocumentCascadeDeletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_deleting_partner_contact_removes_photo_and_cv_documents_and_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser(['delete partner contacts']);
        [$country, $organization] = $this->createCountryAndOrganization();

        $photoDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'file_name' => 'contact-photo.jpg',
            'file_path' => '2026/03/contact-photo.jpg',
            'file_ext' => 'jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $cvDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'file_name' => 'contact-cv.pdf',
            'file_path' => '2026/03/contact-cv.pdf',
            'file_ext' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);

        $contact = PartnerContact::query()->create([
            'partner_organization_id' => $organization->id,
            'full_name_ru' => 'Contact RU',
            'full_name_uz' => 'Contact UZ',
            'photo' => $photoDocument->id,
            'cv' => $cvDocument->id,
        ]);

        $this->actingAs($user)
            ->delete(route('partner-contacts.destroy', $contact))
            ->assertRedirect(route('partner-contacts.index'));

        $this->assertModelMissing($contact);
        $this->assertDatabaseMissing('documents', ['id' => $photoDocument->id]);
        $this->assertDatabaseMissing('documents', ['id' => $cvDocument->id]);
        Storage::disk('documents')->assertMissing('2026/03/contact-photo.jpg');
        Storage::disk('documents')->assertMissing('2026/03/contact-cv.pdf');
    }

    public function test_deleting_agreement_removes_only_agreement_documents(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser(['delete agreements']);
        [$country, $organization] = $this->createCountryAndOrganization();

        $agreement = Agreement::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'title_ru' => 'Agreement RU',
            'title_uz' => 'Agreement UZ',
            'status' => 'draft',
        ]);

        $event = Event::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'agreement_id' => $agreement->id,
            'title_ru' => 'Event RU',
            'title_uz' => 'Event UZ',
            'start_datetime' => '2026-04-10 09:00:00',
            'format' => 'offline',
            'status' => 'rejada',
        ]);

        $agreementDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'agreement_id' => $agreement->id,
            'file_name' => 'agreement-file.pdf',
            'file_path' => '2026/03/agreement-file.pdf',
            'file_ext' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);

        $eventDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'agreement_id' => $agreement->id,
            'event_id' => $event->id,
            'file_name' => 'event-file.pdf',
            'file_path' => '2026/03/event-file.pdf',
            'file_ext' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($user)
            ->delete(route('agreements.destroy', $agreement))
            ->assertRedirect(route('agreements.index'));

        $this->assertDatabaseMissing('documents', ['id' => $agreementDocument->id]);
        $this->assertDatabaseHas('documents', ['id' => $eventDocument->id]);
        $this->assertDatabaseHas('events', ['id' => $event->id, 'agreement_id' => null]);
        Storage::disk('documents')->assertMissing('2026/03/agreement-file.pdf');
        Storage::disk('documents')->assertExists('2026/03/event-file.pdf');
    }

    public function test_deleting_event_removes_event_documents_and_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser(['delete events']);
        [$country, $organization] = $this->createCountryAndOrganization();

        $event = Event::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'title_ru' => 'Event RU',
            'title_uz' => 'Event UZ',
            'start_datetime' => '2026-04-10 09:00:00',
            'format' => 'offline',
            'status' => 'rejada',
        ]);

        $eventDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'event_id' => $event->id,
            'file_name' => 'event-program.docx',
            'file_path' => '2026/03/event-program.docx',
            'file_ext' => 'docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $this->actingAs($user)
            ->delete(route('events.destroy', $event))
            ->assertRedirect(route('events.index'));

        $this->assertModelMissing($event);
        $this->assertDatabaseMissing('documents', ['id' => $eventDocument->id]);
        Storage::disk('documents')->assertMissing('2026/03/event-program.docx');
    }

    public function test_deleting_visit_removes_visit_documents_and_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser(['delete visits']);
        [$country, $organization] = $this->createCountryAndOrganization();

        $visit = Visit::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'title_ru' => 'Visit RU',
            'title_uz' => 'Visit UZ',
            'start_date' => '2026-05-12',
            'status' => 'planned',
        ]);

        $visitDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'visit_id' => $visit->id,
            'file_name' => 'visit-briefing.pdf',
            'file_path' => '2026/03/visit-briefing.pdf',
            'file_ext' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($user)
            ->delete(route('visits.destroy', $visit))
            ->assertRedirect(route('visits.index'));

        $this->assertModelMissing($visit);
        $this->assertDatabaseMissing('documents', ['id' => $visitDocument->id]);
        Storage::disk('documents')->assertMissing('2026/03/visit-briefing.pdf');
    }

    public function test_deleting_partner_organization_removes_only_direct_organization_documents(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser(['delete partner organizations']);
        [$country, $organization] = $this->createCountryAndOrganization();

        $agreement = Agreement::query()->create([
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'title_ru' => 'Agreement RU',
            'title_uz' => 'Agreement UZ',
            'status' => 'draft',
        ]);

        $organizationInfoDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'file_name' => 'organization-info.pdf',
            'file_path' => '2026/03/organization-info.pdf',
            'file_ext' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);

        $organization->update([
            'organization_info_document_id' => $organizationInfoDocument->id,
        ]);

        $organizationDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'file_name' => 'organization-direct.docx',
            'file_path' => '2026/03/organization-direct.docx',
            'file_ext' => 'docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $agreementDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'agreement_id' => $agreement->id,
            'file_name' => 'agreement-keep.pdf',
            'file_path' => '2026/03/agreement-keep.pdf',
            'file_ext' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($user)
            ->delete(route('partner-organizations.destroy', $organization))
            ->assertRedirect(route('partner-organizations.index'));

        $this->assertModelMissing($organization);
        $this->assertDatabaseMissing('documents', ['id' => $organizationInfoDocument->id]);
        $this->assertDatabaseMissing('documents', ['id' => $organizationDocument->id]);
        $this->assertDatabaseHas('documents', ['id' => $agreementDocument->id]);
        $this->assertDatabaseHas('agreements', ['id' => $agreement->id, 'partner_organization_id' => null]);
        Storage::disk('documents')->assertMissing('2026/03/organization-info.pdf');
        Storage::disk('documents')->assertMissing('2026/03/organization-direct.docx');
        Storage::disk('documents')->assertExists('2026/03/agreement-keep.pdf');
    }

    public function test_deleting_country_removes_direct_country_documents_and_files(): void
    {
        Storage::fake('documents');

        $user = $this->authorizedUser(['delete countries']);
        $country = $this->createCountry();

        $countryDocument = $this->createDocument($user, [
            'country_id' => $country->id,
            'file_name' => 'country-note.pdf',
            'file_path' => '2026/03/country-note.pdf',
            'file_ext' => 'pdf',
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($user)
            ->delete(route('countries.destroy', $country))
            ->assertRedirect(route('countries.index'));

        $this->assertModelMissing($country);
        $this->assertDatabaseMissing('documents', ['id' => $countryDocument->id]);
        Storage::disk('documents')->assertMissing('2026/03/country-note.pdf');
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
    private function createCountryAndOrganization(): array
    {
        $country = $this->createCountry();

        $organization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => 'Organization RU',
            'name_uz' => 'Organization UZ',
            'status' => 'faol',
        ]);

        return [$country, $organization];
    }

    private function createCountry(): Country
    {
        return Country::query()->create([
            'name_ru' => 'Country RU',
            'name_uz' => 'Country UZ',
            'iso2' => fake()->unique()->bothify('??'),
            'iso3' => fake()->unique()->bothify('???'),
            'cooperation_status' => 'faol',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createDocument(User $user, array $attributes): Document
    {
        $filePath = (string) $attributes['file_path'];
        Storage::disk('documents')->put($filePath, 'test-file');

        return Document::query()->create(array_merge([
            'title_ru' => null,
            'title_uz' => null,
            'document_number' => null,
            'document_type_id' => null,
            'country_id' => null,
            'partner_organization_id' => null,
            'agreement_id' => null,
            'visit_id' => null,
            'event_id' => null,
            'file_name' => basename($filePath),
            'file_path' => $filePath,
            'file_ext' => pathinfo($filePath, PATHINFO_EXTENSION) ?: null,
            'file_size' => strlen('test-file'),
            'mime_type' => 'application/octet-stream',
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ], $attributes));
    }
}

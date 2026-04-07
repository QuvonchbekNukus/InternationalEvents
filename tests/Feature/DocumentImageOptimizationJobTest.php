<?php

namespace Tests\Feature;

use App\Jobs\OptimizeDocumentImage;
use App\Models\Country;
use App\Models\Document;
use App\Models\PartnerOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentImageOptimizationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_converts_png_document_to_webp_and_replaces_file(): void
    {
        Storage::fake('documents');

        $country = Country::query()->create([
            'name_ru' => 'Страна',
            'name_uz' => 'Davlat',
            'iso2' => 'IM',
            'iso3' => 'IMG',
            'cooperation_status' => 'faol',
        ]);

        $organization = PartnerOrganization::query()->create([
            'country_id' => $country->id,
            'name_ru' => 'Организация',
            'name_uz' => 'Tashkilot',
            'status' => 'faol',
        ]);

        $user = User::factory()->create();

        $originalPath = '2026/04/sample.png';
        Storage::disk('documents')->put($originalPath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aK3sAAAAASUVORK5CYII='));

        $document = Document::query()->create([
            'title_ru' => 'Image RU',
            'title_uz' => 'Image UZ',
            'file_name' => 'sample.png',
            'file_path' => $originalPath,
            'file_ext' => 'png',
            'file_size' => 1,
            'mime_type' => 'image/png',
            'country_id' => $country->id,
            'partner_organization_id' => $organization->id,
            'uploaded_by' => $user->id,
            'status' => 'faol',
            'is_confidential' => false,
        ]);

        (new OptimizeDocumentImage($document->id))->handle();

        $document->refresh();

        if ($document->file_ext !== 'webp') {
            $this->markTestSkipped('GD WebP conversion support is not available in this PHP runtime.');
        }

        $this->assertSame('webp', $document->file_ext);
        $this->assertSame('image/webp', $document->mime_type);
        $this->assertStringEndsWith('.webp', $document->file_name);
        $this->assertStringEndsWith('.webp', $document->file_path);
        $this->assertTrue(Storage::disk('documents')->exists($document->file_path));
        $this->assertFalse(Storage::disk('documents')->exists($originalPath));
    }
}

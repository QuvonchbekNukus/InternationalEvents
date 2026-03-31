<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnerContactController extends Controller implements HasMiddleware
{
    private const STORAGE_DISK = 'documents';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view partner contacts', only: ['index', 'show', 'previewAttachment', 'downloadAttachment']),
            new Middleware('permission:create partner contacts', only: ['create', 'store']),
            new Middleware('permission:edit partner contacts', only: ['edit', 'update']),
            new Middleware('permission:delete partner contacts', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $searchDate = $this->parseBirthdaySearch($search);
        $selectedOrganization = trim((string) $request->string('partner_organization_id'));
        $selectedPrimary = trim((string) $request->string('primary'));

        $partnerContacts = PartnerContact::query()
            ->with([
                'partnerOrganization:id,name_uz,name_ru,name_cryl,short_name,country_id',
                'partnerOrganization.country:id,name_uz,name_ru,name_cryl,iso2',
                'photoDocument:id,file_name,file_ext,file_size,file_path',
                'cvDocument:id,file_name,file_ext,file_size,file_path',
            ])
            ->when($search !== '', function ($query) use ($search, $searchDate) {
                $query->where(function ($partnerContactQuery) use ($search, $searchDate) {
                    $partnerContactQuery
                        ->where('full_name_uz', 'like', "%{$search}%")
                        ->orWhere('full_name_ru', 'like', "%{$search}%")
                        ->orWhere('full_name_cryl', 'like', "%{$search}%")
                        ->orWhere('position_uz', 'like', "%{$search}%")
                        ->orWhere('position_ru', 'like', "%{$search}%")
                        ->orWhere('position_cryl', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('partnerOrganization', fn ($organizationQuery) => $organizationQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%"));

                    if ($searchDate) {
                        $partnerContactQuery->orWhereDate('birthday', $searchDate);
                    }
                });
            })
            ->when($selectedOrganization !== '', fn ($query) => $query->where('partner_organization_id', (int) $selectedOrganization))
            ->when($selectedPrimary !== '', fn ($query) => $query->where('is_primary', $selectedPrimary === '1'))
            ->orderByDesc('is_primary')
            ->orderBy('full_name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('partner-contacts.index', [
            'partnerContacts' => $partnerContacts,
            'partnerOrganizations' => PartnerOrganization::query()
                ->with('country:id,name_uz,name_ru,name_cryl,iso2')
                ->orderBy('name_uz')
                ->get(['id', 'country_id', 'name_uz', 'name_ru', 'name_cryl', 'short_name']),
            'filters' => [
                'search' => $search,
                'partner_organization_id' => $selectedOrganization,
                'primary' => $selectedPrimary,
            ],
        ]);
    }

    public function create(): View
    {
        return view('partner-contacts.create', [
            'partnerContact' => new PartnerContact([
                'is_primary' => false,
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function show(PartnerContact $partnerContact): View
    {
        $partnerContact->load([
            'partnerOrganization:id,country_id,organization_type_id,name_uz,name_ru,name_cryl,short_name,website,city,status',
            'partnerOrganization.country:id,name_uz,name_ru,name_cryl,iso2',
            'partnerOrganization.organizationType:id,name_uz,name_ru,name_cryl',
            'photoDocument:id,title_uz,title_ru,title_cryl,file_name,file_ext,file_size,file_path,mime_type,created_at',
            'cvDocument:id,title_uz,title_ru,title_cryl,file_name,file_ext,file_size,file_path,mime_type,created_at',
        ]);

        return view('partner-contacts.show', [
            'partnerContact' => $partnerContact,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $partnerContact = DB::transaction(function () use ($request, $validated) {
            $partnerContact = PartnerContact::query()->create($validated);
            $this->syncPrimaryContact($partnerContact);
            $this->syncAttachments($request, $partnerContact->refresh());

            return $partnerContact->refresh();
        });

        return redirect()
            ->route('partner-contacts.index')
            ->with('status', "Hamkor kontakt {$partnerContact->display_name} muvaffaqiyatli yaratildi.");
    }

    public function edit(PartnerContact $partnerContact): View
    {
        return view('partner-contacts.edit', [
            'partnerContact' => $partnerContact,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, PartnerContact $partnerContact): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($request, $partnerContact, $validated) {
            $partnerContact->update($validated);
            $partnerContact = $partnerContact->refresh();
            $this->syncPrimaryContact($partnerContact);
            $this->syncAttachments($request, $partnerContact->refresh());
        });

        return redirect()
            ->route('partner-contacts.index')
            ->with('status', "Hamkor kontakt {$partnerContact->display_name} yangilandi.");
    }

    public function destroy(PartnerContact $partnerContact): RedirectResponse
    {
        $partnerContactName = $partnerContact->display_name;
        $partnerContact->delete();

        return redirect()
            ->route('partner-contacts.index')
            ->with('status', "Hamkor kontakt {$partnerContactName} o'chirildi.");
    }

    public function previewAttachment(Request $request, PartnerContact $partnerContact, string $type): StreamedResponse
    {
        $document = $this->resolvedAttachmentDocument($partnerContact, $type);

        return Storage::disk(self::STORAGE_DISK)->response(
            $document->file_path,
            $document->file_name,
            $document->mime_type ? ['Content-Type' => $document->mime_type] : [],
        );
    }

    public function downloadAttachment(Request $request, PartnerContact $partnerContact, string $type): StreamedResponse
    {
        $document = $this->resolvedAttachmentDocument($partnerContact, $type);

        return Storage::disk(self::STORAGE_DISK)->download($document->file_path, $document->file_name);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'partner_organization_id' => ['required', 'integer', 'exists:partner_organizations,id'],
            'full_name_ru' => ['required', 'string', 'max:255'],
            'full_name_uz' => ['required', 'string', 'max:255'],
            'full_name_cryl' => ['required', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'position_ru' => ['nullable', 'string', 'max:255'],
            'position_uz' => ['nullable', 'string', 'max:255'],
            'position_cryl' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_primary' => ['sometimes', 'boolean'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:51200'],
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');
        unset($validated['photo_file'], $validated['cv_file']);

        return $validated;
    }

    /**
     * @return array{partnerOrganizations: \Illuminate\Database\Eloquent\Collection<int, PartnerOrganization>}
     */
    private function formOptions(): array
    {
        return [
            'partnerOrganizations' => PartnerOrganization::query()
                ->with('country:id,name_uz,name_ru,name_cryl,iso2')
                ->orderBy('name_uz')
                ->get(['id', 'country_id', 'name_uz', 'name_ru', 'name_cryl', 'short_name']),
        ];
    }

    private function syncPrimaryContact(PartnerContact $partnerContact): void
    {
        if (! $partnerContact->is_primary) {
            return;
        }

        PartnerContact::query()
            ->where('partner_organization_id', $partnerContact->partner_organization_id)
            ->whereKeyNot($partnerContact->id)
            ->update(['is_primary' => false]);
    }

    private function syncAttachments(Request $request, PartnerContact $partnerContact): void
    {
        foreach (['photo' => 'photo_file', 'cv' => 'cv_file'] as $attribute => $inputName) {
            $document = null;

            if ($request->hasFile($inputName)) {
                $document = $this->upsertAttachmentDocument(
                    $partnerContact,
                    $attribute,
                    $request->file($inputName),
                    (int) $request->user()->id,
                );

                if ((int) $partnerContact->getAttribute($attribute) !== (int) $document->id) {
                    $partnerContact->forceFill([$attribute => $document->id])->save();
                }
            } else {
                $document = $this->attachmentDocument($partnerContact, $attribute);
            }

            if ($document) {
                $this->syncAttachmentDocumentMetadata($partnerContact, $attribute, $document);
            }
        }
    }

    private function upsertAttachmentDocument(
        PartnerContact $partnerContact,
        string $attribute,
        UploadedFile $file,
        int $uploadedBy
    ): Document {
        $document = $this->attachmentDocument($partnerContact, $attribute);
        $oldFilePath = $document?->file_path;
        $payload = array_merge(
            $this->attachmentDocumentMetadata($partnerContact, $attribute),
            $this->uploadedFilePayload($file),
            [
                'uploaded_by' => $uploadedBy,
                'status' => 'faol',
                'is_confidential' => false,
            ],
        );

        if ($document) {
            $document->update($payload);

            if ($oldFilePath && $oldFilePath !== $document->file_path) {
                Storage::disk(self::STORAGE_DISK)->delete($oldFilePath);
            }

            return $document->refresh();
        }

        return Document::query()->create($payload);
    }

    private function syncAttachmentDocumentMetadata(PartnerContact $partnerContact, string $attribute, Document $document): void
    {
        $document->fill($this->attachmentDocumentMetadata($partnerContact, $attribute));

        if ($document->isDirty()) {
            $document->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function attachmentDocumentMetadata(PartnerContact $partnerContact, string $attribute): array
    {
        $partnerContact->loadMissing('partnerOrganization:id,country_id');
        $contactNameUz = $partnerContact->full_name_uz ?: $partnerContact->display_name;
        $contactNameRu = $partnerContact->full_name_ru ?: $partnerContact->display_name;
        $contactNameCryl = $partnerContact->full_name_cryl ?: $partnerContact->display_name;

        return [
            'title_uz' => match ($attribute) {
                'photo' => "{$contactNameUz} fotosurati",
                default => "{$contactNameUz} CV",
            },
            'title_ru' => match ($attribute) {
                'photo' => "Фото {$contactNameRu}",
                default => "CV {$contactNameRu}",
            },
            'title_cryl' => match ($attribute) {
                'photo' => "{$contactNameCryl} фото",
                default => "{$contactNameCryl} CV",
            },
            'document_number' => null,
            'document_type_id' => null,
            'country_id' => $partnerContact->partnerOrganization?->country_id,
            'partner_organization_id' => $partnerContact->partner_organization_id,
            'agreement_id' => null,
            'visit_id' => null,
            'event_id' => null,
            'description' => match ($attribute) {
                'photo' => 'Hamkor kontakt rasmi',
                default => 'Hamkor kontakt CV hujjati',
            },
        ];
    }

    /**
     * @return array{file_name: string, file_path: string, file_ext: ?string, file_size: int, mime_type: ?string}
     */
    private function uploadedFilePayload(UploadedFile $file): array
    {
        $filePath = $file->store(now()->format('Y/m'), self::STORAGE_DISK);

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_ext' => $file->getClientOriginalExtension() ?: null,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
        ];
    }

    private function attachmentDocument(PartnerContact $partnerContact, string $attribute): ?Document
    {
        $documentId = $partnerContact->getAttribute($attribute);

        if (! $documentId) {
            return null;
        }

        return Document::query()->find($documentId);
    }

    private function resolvedAttachmentDocument(PartnerContact $partnerContact, string $type): Document
    {
        abort_unless(in_array($type, ['photo', 'cv'], true), 404);

        $document = $this->attachmentDocument($partnerContact, $type);

        abort_unless($document?->file_path && Storage::disk(self::STORAGE_DISK)->exists($document->file_path), 404);

        return $document;
    }

    private function parseBirthdaySearch(string $search): ?string
    {
        if ($search === '') {
            return null;
        }

        foreach (['Y-m-d', 'd.m.Y', 'd-m-Y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $search);
            } catch (\Throwable) {
                continue;
            }

            if (! $parsed) {
                continue;
            }

            if ($parsed->format($format) === $search) {
                return $parsed->format('Y-m-d');
            }
        }

        return null;
    }
}

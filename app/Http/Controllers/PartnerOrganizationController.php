<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Country;
use App\Models\Document;
use App\Models\Event;
use App\Models\OrganizationType;
use App\Models\PartnerOrganization;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartnerOrganizationController extends Controller implements HasMiddleware
{
    private const STORAGE_DISK = 'documents';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view partner organizations', only: ['index', 'show']),
            new Middleware('permission:create partner organizations', only: ['create', 'store']),
            new Middleware('permission:edit partner organizations', only: ['edit', 'update']),
            new Middleware('permission:delete partner organizations', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCountry = trim((string) $request->string('country_id'));
        $selectedType = trim((string) $request->string('organization_type_id'));
        $selectedStatus = trim((string) $request->string('status'));

        $partnerOrganizations = PartnerOrganization::query()
            ->with(['country:id,name_uz,name_ru,name_cryl,iso2', 'organizationType:id,name_uz,name_ru,name_cryl'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($partnerOrganizationQuery) use ($search) {
                    $partnerOrganizationQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%")
                        ->orWhere('short_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('website', 'like', "%{$search}%")
                        ->orWhereHas('country', fn ($countryQuery) => $countryQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%")
                            ->orWhere('iso2', 'like', "%{$search}%"))
                        ->orWhereHas('organizationType', fn ($typeQuery) => $typeQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%"));
                });
            })
            ->when($selectedCountry !== '', fn ($query) => $query->where('country_id', (int) $selectedCountry))
            ->when($selectedType !== '', fn ($query) => $query->where('organization_type_id', (int) $selectedType))
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('partner-organizations.index', [
            'partnerOrganizations' => $partnerOrganizations,
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'organizationTypes' => OrganizationType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'statuses' => PartnerOrganization::STATUS_LABELS,
            'filters' => [
                'search' => $search,
                'country_id' => $selectedCountry,
                'organization_type_id' => $selectedType,
                'status' => $selectedStatus,
            ],
        ]);
    }

    public function create(): View
    {
        return view('partner-organizations.create', [
            'partnerOrganization' => new PartnerOrganization([
                'status' => 'faol',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function show(Request $request, PartnerOrganization $partnerOrganization): View
    {
        $partnerOrganization->load([
            'country:id,name_uz,name_ru,name_cryl,iso2,iso3',
            'organizationType:id,name_uz,name_ru,name_cryl',
            'organizationInfoDocument:id,title_uz,title_ru,title_cryl,file_name,file_ext,file_size,file_path,mime_type,created_at',
        ]);

        $canViewPartnerContacts = (bool) $request->user()?->can('view partner contacts');
        $canViewAgreements = (bool) $request->user()?->can('view agreements')
            || (bool) $request->user()?->can('view own agreements');
        $canViewVisits = (bool) $request->user()?->can('view visits')
            || (bool) $request->user()?->can('view own visits');
        $canViewEvents = (bool) $request->user()?->can('view events')
            || (bool) $request->user()?->can('view own events');
        $canViewDocuments = (bool) $request->user()?->can('view documents')
            || (bool) $request->user()?->can('view own documents');

        $partnerContacts = $canViewPartnerContacts
            ? $partnerOrganization->partnerContacts()
                ->with([
                    'photoDocument:id,file_name,file_ext,file_size,file_path,mime_type',
                    'cvDocument:id,file_name,file_ext,file_size,file_path,mime_type',
                ])
                ->orderByDesc('is_primary')
                ->orderByRaw('coalesce(full_name_uz, full_name_ru, full_name_cryl) asc')
                ->get()
            : collect();

        return view('partner-organizations.show', [
            'partnerOrganization' => $partnerOrganization,
            'statuses' => PartnerOrganization::STATUS_LABELS,
            'partnerContacts' => $partnerContacts,
            'agreements' => $this->visibleAgreements($request, $partnerOrganization),
            'visits' => $this->visibleVisits($request, $partnerOrganization),
            'events' => $this->visibleEvents($request, $partnerOrganization),
            'documents' => $this->visibleDocuments($request, $partnerOrganization),
            'relatedAccess' => [
                'partner_contacts' => $canViewPartnerContacts,
                'agreements' => $canViewAgreements,
                'visits' => $canViewVisits,
                'events' => $canViewEvents,
                'documents' => $canViewDocuments,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $partnerOrganization = DB::transaction(function () use ($request, $validated) {
            $partnerOrganization = PartnerOrganization::query()->create($validated);
            $this->syncOrganizationInfoDocument($request, $partnerOrganization->refresh(), (int) $request->user()->id);

            return $partnerOrganization->refresh();
        });

        return redirect()
            ->route('partner-organizations.index')
            ->with('status', "Hamkor tashkilot {$partnerOrganization->display_name} muvaffaqiyatli yaratildi.");
    }

    public function edit(PartnerOrganization $partnerOrganization): View
    {
        $partnerOrganization->load('organizationInfoDocument:id,title_uz,title_ru,title_cryl,file_name,file_ext,file_size,file_path,mime_type');

        return view('partner-organizations.edit', [
            'partnerOrganization' => $partnerOrganization,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, PartnerOrganization $partnerOrganization): RedirectResponse
    {
        $validated = $this->validatedData($request);

        DB::transaction(function () use ($request, $partnerOrganization, $validated) {
            $partnerOrganization->update($validated);
            $this->syncOrganizationInfoDocument($request, $partnerOrganization->refresh(), (int) $request->user()->id);
        });

        return redirect()
            ->route('partner-organizations.index')
            ->with('status', "Hamkor tashkilot {$partnerOrganization->display_name} yangilandi.");
    }

    public function destroy(PartnerOrganization $partnerOrganization): RedirectResponse
    {
        if ($partnerOrganization->partnerContacts()->exists()) {
            return back()->with('error', "Hamkor tashkilotga kontaktlar biriktirilgan. Avval ularni boshqa tashkilotga o'tkazing yoki o'chiring.");
        }

        $partnerOrganizationName = $partnerOrganization->display_name;
        $organizationInfoDocument = $partnerOrganization->organizationInfoDocument;
        $partnerOrganization->delete();

        if ($organizationInfoDocument) {
            $filePath = $organizationInfoDocument->file_path;
            $organizationInfoDocument->delete();

            if ($filePath) {
                Storage::disk(self::STORAGE_DISK)->delete($filePath);
            }
        }

        return redirect()
            ->route('partner-organizations.index')
            ->with('status', "Hamkor tashkilot {$partnerOrganizationName} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'name_ru' => ['required', 'string', 'max:255'],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_cryl' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'organization_type_id' => ['nullable', 'integer', 'exists:organization_types,id'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(PartnerOrganization::STATUSES)],
            'notes' => ['nullable', 'string'],
            'organization_info_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:51200'],
        ]);

        unset($validated['organization_info_file']);

        return $validated;
    }

    /**
     * @return array{countries: \Illuminate\Database\Eloquent\Collection<int, Country>, organizationTypes: \Illuminate\Database\Eloquent\Collection<int, OrganizationType>, statuses: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'organizationTypes' => OrganizationType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'statuses' => PartnerOrganization::STATUS_LABELS,
        ];
    }

    private function visibleAgreements(Request $request, PartnerOrganization $partnerOrganization)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view agreements') && ! $user->can('view own agreements'))) {
            return collect();
        }

        $query = $partnerOrganization->agreements()->with([
            'agreementType:id,name_uz,name_ru,name_cryl',
            'agreementDirection:id,name_uz,name_ru,name_cryl',
        ]);

        if (! $user->can('view agreements') && $user->can('view own agreements')) {
            $query->where(function ($agreementQuery) use ($user) {
                $agreementQuery
                    ->where('responsible_user_id', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        }

        return $query
            ->orderByDesc('signed_date')
            ->orderByRaw('coalesce(title_uz, title_ru, title_cryl) asc')
            ->get();
    }

    private function visibleVisits(Request $request, PartnerOrganization $partnerOrganization)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view visits') && ! $user->can('view own visits'))) {
            return collect();
        }

        $query = $partnerOrganization->visits()->with([
            'visitType:id,name_uz,name_ru,name_cryl',
        ]);

        if (! $user->can('view visits') && $user->can('view own visits')) {
            $query->where(function ($visitQuery) use ($user) {
                $visitQuery
                    ->where('responsible_user_id', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        }

        return $query
            ->orderByDesc('start_date')
            ->orderByRaw('coalesce(title_uz, title_ru, title_cryl) asc')
            ->get();
    }

    private function visibleEvents(Request $request, PartnerOrganization $partnerOrganization)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view events') && ! $user->can('view own events'))) {
            return collect();
        }

        $query = $partnerOrganization->events()->with([
            'eventType:id,name_uz,name_ru,name_cryl',
            'agreement:id,title_uz,title_ru,title_cryl,short_title_uz,short_title_ru,short_title_cryl',
        ]);

        if (! $user->can('view events') && $user->can('view own events')) {
            $query->where(function ($eventQuery) use ($user) {
                $eventQuery
                    ->where('responsible_user_id', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        }

        return $query
            ->orderByDesc('start_datetime')
            ->orderByRaw('coalesce(title_uz, title_ru, title_cryl) asc')
            ->get();
    }

    private function visibleDocuments(Request $request, PartnerOrganization $partnerOrganization)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view documents') && ! $user->can('view own documents'))) {
            return collect();
        }

        $query = $partnerOrganization->documents()->with([
            'documentType:id,name_uz,name_ru,name_cryl',
            'agreement:id,title_uz,title_ru,title_cryl,short_title_uz,short_title_ru,short_title_cryl',
            'visit:id,title_uz,title_ru,title_cryl',
            'event:id,title_uz,title_ru,title_cryl',
            'uploader:id,first_name,middle_name,last_name',
        ]);

        if (! $user->can('view documents') && $user->can('view own documents')) {
            $query->where('uploaded_by', $user->id);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByRaw('coalesce(title_uz, title_ru, title_cryl, file_name) asc')
            ->get();
    }

    private function syncOrganizationInfoDocument(Request $request, PartnerOrganization $partnerOrganization, int $uploadedBy): void
    {
        if (! $request->hasFile('organization_info_file')) {
            $document = $partnerOrganization->organizationInfoDocument;

            if ($document) {
                $this->syncOrganizationInfoDocumentMetadata($partnerOrganization, $document);
            }

            return;
        }

        $document = $this->upsertOrganizationInfoDocument(
            $partnerOrganization,
            $request->file('organization_info_file'),
            $uploadedBy,
        );

        if ((int) $partnerOrganization->organization_info_document_id !== (int) $document->id) {
            $partnerOrganization->forceFill([
                'organization_info_document_id' => $document->id,
            ])->save();
        }
    }

    private function upsertOrganizationInfoDocument(
        PartnerOrganization $partnerOrganization,
        UploadedFile $file,
        int $uploadedBy
    ): Document {
        $document = $partnerOrganization->organizationInfoDocument;
        $oldFilePath = $document?->file_path;
        $payload = array_merge(
            $this->organizationInfoDocumentMetadata($partnerOrganization),
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

    private function syncOrganizationInfoDocumentMetadata(PartnerOrganization $partnerOrganization, Document $document): void
    {
        $document->fill($this->organizationInfoDocumentMetadata($partnerOrganization));

        if ($document->isDirty()) {
            $document->save();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function organizationInfoDocumentMetadata(PartnerOrganization $partnerOrganization): array
    {
        $organizationNameUz = $partnerOrganization->name_uz ?: $partnerOrganization->display_name;
        $organizationNameRu = $partnerOrganization->name_ru ?: $partnerOrganization->display_name;
        $organizationNameCryl = $partnerOrganization->name_cryl ?: $partnerOrganization->display_name;

        return [
            'title_uz' => "{$organizationNameUz} info fayli",
            'title_ru' => "{$organizationNameRu} info file",
            'title_cryl' => "{$organizationNameCryl} info fayli",
            'document_number' => null,
            'document_type_id' => null,
            'country_id' => $partnerOrganization->country_id,
            'partner_organization_id' => $partnerOrganization->id,
            'agreement_id' => null,
            'visit_id' => null,
            'event_id' => null,
            'description' => "Hamkor tashkilotning info fayli",
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Country;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Event;
use App\Models\PartnerOrganization;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view documents|view own documents', only: ['index', 'download']),
            new Middleware('permission:create documents', only: ['create', 'store']),
            new Middleware('permission:edit documents|edit own documents', only: ['edit', 'update']),
            new Middleware('permission:delete documents', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedDocumentType = trim((string) $request->string('document_type_id'));
        $selectedStatus = trim((string) $request->string('status'));
        $selectedConfidential = trim((string) $request->string('is_confidential'));

        $documentsQuery = Document::query()->with([
            'documentType:id,name_uz,name_ru,name_cryl',
            'country:id,name_uz,name_ru,name_cryl,iso2',
            'partnerOrganization:id,name_uz,name_ru,name_cryl,short_name',
            'agreement:id,title_uz,title_ru,title_cryl,short_title_uz,short_title_ru,short_title_cryl',
            'visit:id,title_uz,title_ru,title_cryl',
            'event:id,title_uz,title_ru,title_cryl',
            'uploader:id,first_name,middle_name,last_name',
        ]);

        $this->applyOwnScope(
            $request,
            $documentsQuery,
            'view documents',
            'view own documents',
            fn ($query, $user): mixed => $query->where('uploaded_by', $user->id)
        );

        $documents = $documentsQuery
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($documentQuery) use ($search) {
                    $documentQuery
                        ->where('title_uz', 'like', "%{$search}%")
                        ->orWhere('title_ru', 'like', "%{$search}%")
                        ->orWhere('title_cryl', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhere('file_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('documentType', fn ($documentTypeQuery) => $documentTypeQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%"))
                        ->orWhereHas('country', fn ($countryQuery) => $countryQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%")
                            ->orWhere('iso2', 'like', "%{$search}%"))
                        ->orWhereHas('partnerOrganization', fn ($organizationQuery) => $organizationQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%"))
                        ->orWhereHas('agreement', fn ($agreementQuery) => $agreementQuery
                            ->where('title_uz', 'like', "%{$search}%")
                            ->orWhere('title_ru', 'like', "%{$search}%")
                            ->orWhere('title_cryl', 'like', "%{$search}%")
                            ->orWhere('short_title_uz', 'like', "%{$search}%")
                            ->orWhere('short_title_ru', 'like', "%{$search}%")
                            ->orWhere('short_title_cryl', 'like', "%{$search}%"))
                        ->orWhereHas('visit', fn ($visitQuery) => $visitQuery
                            ->where('title_uz', 'like', "%{$search}%")
                            ->orWhere('title_ru', 'like', "%{$search}%")
                            ->orWhere('title_cryl', 'like', "%{$search}%"))
                        ->orWhereHas('event', fn ($eventQuery) => $eventQuery
                            ->where('title_uz', 'like', "%{$search}%")
                            ->orWhere('title_ru', 'like', "%{$search}%")
                            ->orWhere('title_cryl', 'like', "%{$search}%"))
                        ->orWhereHas('uploader', fn ($uploaderQuery) => $uploaderQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"));
                });
            })
            ->when($selectedDocumentType !== '', fn ($query) => $query->where('document_type_id', (int) $selectedDocumentType))
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->when($selectedConfidential !== '', fn ($query) => $query->where('is_confidential', $selectedConfidential === '1'))
            ->orderByDesc('created_at')
            ->orderBy('title_uz')
            ->paginate(10)
            ->withQueryString();

        return view('documents.index', [
            'documents' => $documents,
            'documentTypes' => DocumentType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'statuses' => Document::STATUS_LABELS,
            'filters' => [
                'search' => $search,
                'document_type_id' => $selectedDocumentType,
                'status' => $selectedStatus,
                'is_confidential' => $selectedConfidential,
            ],
        ]);
    }

    public function create(): View
    {
        return view('documents.create', [
            'document' => new Document([
                'status' => 'faol',
                'is_confidential' => false,
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request, requiresFile: true);
        $validated['uploaded_by'] = $request->user()?->id;
        $validated = array_merge($validated, $this->uploadedFilePayload($request->file('file')));

        $document = Document::create($validated);

        return redirect()
            ->route('documents.index')
            ->with('status', "Hujjat {$document->display_title} muvaffaqiyatli yaratildi.");
    }

    public function edit(Document $document): View
    {
        $this->authorizeOwnedRecord(
            request(),
            $document,
            'edit documents',
            'edit own documents',
            fn (Document $record, $user): bool => (int) $record->uploaded_by === (int) $user->id
        );

        return view('documents.edit', [
            'document' => $document,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        $this->authorizeOwnedRecord(
            $request,
            $document,
            'edit documents',
            'edit own documents',
            fn (Document $record, $user): bool => (int) $record->uploaded_by === (int) $user->id
        );

        $validated = $this->validatedData($request);
        $oldPath = null;

        if ($request->hasFile('file')) {
            $oldPath = $document->file_path;
            $validated = array_merge($validated, $this->uploadedFilePayload($request->file('file')));
        }

        $document->update($validated);

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('documents.index')
            ->with('status', "Hujjat {$document->display_title} yangilandi.");
    }

    public function destroy(Document $document): RedirectResponse
    {
        $documentTitle = $document->display_title;
        $filePath = $document->file_path;

        $document->delete();

        if ($filePath) {
            Storage::disk('public')->delete($filePath);
        }

        return redirect()
            ->route('documents.index')
            ->with('status', "Hujjat {$documentTitle} o'chirildi.");
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->authorizeOwnedRecord(
            $request,
            $document,
            'view documents',
            'view own documents',
            fn (Document $record, $user): bool => (int) $record->uploaded_by === (int) $user->id
        );

        abort_unless($document->file_path && Storage::disk('public')->exists($document->file_path), 404);

        activity('system')
            ->causedBy($request->user())
            ->performedOn($document)
            ->event('downloaded')
            ->withProperties([
                'file_name' => $document->file_name,
                'file_ext' => $document->file_ext,
                'document_number' => $document->document_number,
            ])
            ->log('Hujjat yuklab olindi');

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, bool $requiresFile = false): array
    {
        $validated = $request->validate([
            'title_ru' => ['required', 'string', 'max:255'],
            'title_uz' => ['required', 'string', 'max:255'],
            'title_cryl' => ['required', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:255'],
            'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'partner_organization_id' => ['nullable', 'integer', 'exists:partner_organizations,id'],
            'agreement_id' => ['nullable', 'integer', 'exists:agreements,id'],
            'visit_id' => ['nullable', 'integer', 'exists:visits,id'],
            'event_id' => ['nullable', 'integer', 'exists:events,id'],
            'status' => ['required', 'string', Rule::in(Document::STATUSES)],
            'is_confidential' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'file' => [$requiresFile ? 'required' : 'nullable', 'file', 'max:51200'],
        ]);

        $validated['is_confidential'] = $request->boolean('is_confidential');
        unset($validated['file']);

        $countryIds = collect([
            $validated['country_id'] ?? null,
            $this->relatedCountryId(PartnerOrganization::class, $validated['partner_organization_id'] ?? null),
            $this->relatedCountryId(Agreement::class, $validated['agreement_id'] ?? null),
            $this->relatedCountryId(Visit::class, $validated['visit_id'] ?? null),
            $this->relatedCountryId(Event::class, $validated['event_id'] ?? null),
        ])->filter(fn ($countryId) => $countryId !== null)
            ->map(fn ($countryId) => (int) $countryId)
            ->unique()
            ->values();

        if ($countryIds->count() > 1) {
            throw ValidationException::withMessages([
                'country_id' => "Tanlangan bog'lanmalar turli davlatlarga tegishli. Ularni bir xil davlat bilan tanlang.",
            ]);
        }

        if (($validated['country_id'] ?? null) === null && $countryIds->count() === 1) {
            $validated['country_id'] = $countryIds->first();
        }

        return $validated;
    }

    /**
     * @return array{documentTypes: \Illuminate\Database\Eloquent\Collection<int, DocumentType>, countries: \Illuminate\Database\Eloquent\Collection<int, Country>, partnerOrganizations: \Illuminate\Database\Eloquent\Collection<int, PartnerOrganization>, agreements: \Illuminate\Database\Eloquent\Collection<int, Agreement>, visits: \Illuminate\Database\Eloquent\Collection<int, Visit>, events: \Illuminate\Database\Eloquent\Collection<int, Event>, statuses: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'documentTypes' => DocumentType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'partnerOrganizations' => PartnerOrganization::query()->orderBy('name_uz')->get(['id', 'country_id', 'name_uz', 'name_ru', 'name_cryl', 'short_name']),
            'agreements' => Agreement::query()->orderByDesc('created_at')->get(['id', 'country_id', 'title_uz', 'title_ru', 'title_cryl', 'short_title_uz', 'short_title_ru', 'short_title_cryl']),
            'visits' => Visit::query()->orderByDesc('start_date')->get(['id', 'country_id', 'title_uz', 'title_ru', 'title_cryl', 'start_date']),
            'events' => Event::query()->orderByDesc('start_datetime')->get(['id', 'country_id', 'title_uz', 'title_ru', 'title_cryl', 'start_datetime']),
            'statuses' => Document::STATUS_LABELS,
        ];
    }

    private function relatedCountryId(string $modelClass, ?int $id): ?int
    {
        if ($id === null) {
            return null;
        }

        return $modelClass::query()->whereKey($id)->value('country_id');
    }

    /**
     * @return array{file_name: string, file_path: string, file_ext: ?string, file_size: int, mime_type: ?string}
     */
    private function uploadedFilePayload(UploadedFile $file): array
    {
        $filePath = $file->store('documents/'.now()->format('Y/m'), 'public');

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_ext' => $file->getClientOriginalExtension() ?: null,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
        ];
    }
}

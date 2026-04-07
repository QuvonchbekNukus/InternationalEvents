<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Department;
use App\Models\Document;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitType;
use App\Services\DateReminderNotificationService;
use App\Services\UploadedFileProcessor;
use App\Services\UserNotificationService;
use App\Support\LocaleLabels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VisitController extends Controller implements HasMiddleware
{
    private const STORAGE_DISK = 'documents';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view visits|view own visits', only: ['index', 'show']),
            new Middleware('permission:create visits', only: ['create', 'store']),
            new Middleware('permission:edit visits|edit own visits', only: ['edit', 'update', 'destroyAttachment']),
            new Middleware('permission:delete visits', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCountry = trim((string) $request->string('country_id'));
        $selectedVisitType = trim((string) $request->string('visit_type_id'));
        $selectedDirection = trim((string) $request->string('direction'));
        $selectedStatus = trim((string) $request->string('status'));

        $visitsQuery = Visit::query()->with([
            'country:id,name_uz,name_ru,iso2',
            'visitType:id,name_uz,name_ru',
            'partnerOrganization:id,name_uz,name_ru,short_name,country_id',
            'responsibleUser:id,first_name,middle_name,last_name',
            'responsibleDepartment:id,name_uz,name_ru',
        ]);

        $this->applyOwnScope(
            $request,
            $visitsQuery,
            'view visits',
            'view own visits',
            function ($query, $user): void {
                $query->where(function ($visitQuery) use ($user) {
                    $visitQuery
                        ->where('responsible_user_id', $user->id)
                        ->orWhere('created_by', $user->id);
                });
            }
        );

        $visits = $visitsQuery
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($visitQuery) use ($search) {
                    $visitQuery
                        ->where('title_uz', 'like', "%{$search}%")
                        ->orWhere('title_ru', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('purpose_uz', 'like', "%{$search}%")
                        ->orWhere('purpose_ru', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('country', fn ($countryQuery) => $countryQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('iso2', 'like', "%{$search}%"))
                        ->orWhereHas('visitType', fn ($visitTypeQuery) => $visitTypeQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%"))
                        ->orWhereHas('partnerOrganization', fn ($organizationQuery) => $organizationQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%"))
                        ->orWhereHas('responsibleUser', fn ($userQuery) => $userQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('responsibleDepartment', fn ($departmentQuery) => $departmentQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%"));
                });
            })
            ->when($selectedCountry !== '', fn ($query) => $query->where('country_id', (int) $selectedCountry))
            ->when($selectedVisitType !== '', fn ($query) => $query->where('visit_type_id', (int) $selectedVisitType))
            ->when($selectedDirection !== '', fn ($query) => $query->where('direction', $selectedDirection))
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->orderByDesc('start_date')
            ->orderBy('title_uz')
            ->paginate(10)
            ->withQueryString();

        return view('visits.index', [
            'visits' => $visits,
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'visitTypes' => VisitType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'directions' => LocaleLabels::map(Visit::DIRECTION_TRANSLATION_KEY, Visit::DIRECTIONS),
            'statuses' => LocaleLabels::map(Visit::STATUS_TRANSLATION_KEY, Visit::STATUSES),
            'filters' => [
                'search' => $search,
                'country_id' => $selectedCountry,
                'visit_type_id' => $selectedVisitType,
                'direction' => $selectedDirection,
                'status' => $selectedStatus,
            ],
        ]);
    }

    public function create(): View
    {
        return view('visits.create', [
            'visit' => new Visit([
                'status' => 'planned',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(
        Request $request,
        UserNotificationService $notificationService,
        DateReminderNotificationService $dateReminderNotificationService
    ): RedirectResponse {
        $validated = $this->validatedData($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        $visit = DB::transaction(function () use ($request, $validated) {
            $visit = Visit::query()->create($validated);
            $this->syncVisitDocuments($request, $visit->refresh(), (int) $request->user()->id);

            return $visit->refresh();
        });

        $notificationService->notifyResponsibleUser(
            $visit,
            null,
            $visit->responsible_user_id,
            $request->user(),
            'visit',
            true
        );

        $dateReminderNotificationService->ensureVisitStartReminderFor($visit->fresh());

        return redirect()
            ->route('visits.index')
            ->with('status', "Tashrif {$visit->display_title} muvaffaqiyatli yaratildi.");
    }

    public function show(Request $request, Visit $visit): View
    {
        $this->authorizeViewedRecord(
            $request,
            $visit,
            'view visits',
            'view own visits',
            fn (Visit $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $visit->load([
            'visitType:id,name_uz,name_ru',
            'country:id,name_uz,name_ru,iso2',
            'partnerOrganization:id,name_uz,name_ru,short_name',
            'responsibleUser:id,first_name,middle_name,last_name',
            'responsibleDepartment:id,name_uz,name_ru',
            'creator:id,first_name,middle_name,last_name',
            'updater:id,first_name,middle_name,last_name',
            'documents:id,title_uz,title_ru,file_name,file_ext,file_size,file_path,mime_type,visit_id,uploaded_by,created_at',
            'documents.uploader:id,first_name,middle_name,last_name',
        ]);

        return view('visits.show', [
            'visit' => $visit,
            'directions' => LocaleLabels::map(Visit::DIRECTION_TRANSLATION_KEY, Visit::DIRECTIONS),
            'statuses' => LocaleLabels::map(Visit::STATUS_TRANSLATION_KEY, Visit::STATUSES),
        ]);
    }

    public function edit(Visit $visit): View
    {
        $this->authorizeOwnedRecord(
            request(),
            $visit,
            'edit visits',
            'edit own visits',
            fn (Visit $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $visit->load('documents:id,title_uz,title_ru,file_name,file_ext,file_size,file_path,mime_type,visit_id,uploaded_by,created_at');

        return view('visits.edit', [
            'visit' => $visit,
            ...$this->formOptions(),
        ]);
    }

    public function update(
        Request $request,
        Visit $visit,
        UserNotificationService $notificationService,
        DateReminderNotificationService $dateReminderNotificationService
    ): RedirectResponse {
        $this->authorizeOwnedRecord(
            $request,
            $visit,
            'edit visits',
            'edit own visits',
            fn (Visit $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $previousResponsibleUserId = $visit->responsible_user_id;
        $validated = $this->validatedData($request);
        $validated['updated_by'] = $request->user()?->id;

        $visit = DB::transaction(function () use ($request, $visit, $validated) {
            $visit->update($validated);
            $this->syncVisitDocuments($request, $visit->refresh(), (int) $request->user()->id);

            return $visit->refresh();
        });

        $notificationService->notifyResponsibleUser(
            $visit,
            $previousResponsibleUserId,
            $visit->responsible_user_id,
            $request->user(),
            'visit'
        );

        $dateReminderNotificationService->ensureVisitStartReminderFor($visit->fresh());

        return redirect()
            ->route('visits.index')
            ->with('status', "Tashrif {$visit->display_title} yangilandi.");
    }

    public function destroyAttachment(Request $request, Visit $visit, Document $document): RedirectResponse
    {
        $this->authorizeOwnedRecord(
            $request,
            $visit,
            'edit visits',
            'edit own visits',
            fn (Visit $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $document = $this->resolveVisitDocument($visit, $document);
        $fileName = $document->file_name;
        $document->delete();

        return redirect()
            ->route('visits.edit', $visit)
            ->with('status', "Biriktirilgan fayl {$fileName} o'chirildi.");
    }

    public function destroy(Visit $visit): RedirectResponse
    {
        $visitTitle = $visit->display_title;
        $visit->delete();

        return redirect()
            ->route('visits.index')
            ->with('status', "Tashrif {$visitTitle} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title_ru' => ['required', 'string', 'max:255'],
            'title_uz' => ['required', 'string', 'max:255'],
            'visit_type_id' => ['nullable', 'integer', 'exists:visit_types,id'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'partner_organization_id' => ['nullable', 'integer', 'exists:partner_organizations,id'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'direction' => ['nullable', 'string', Rule::in(Visit::DIRECTIONS)],
            'status' => ['required', 'string', Rule::in(Visit::STATUSES)],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'responsible_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'purpose_ru' => ['nullable', 'string'],
            'purpose_uz' => ['nullable', 'string'],
            'result_summary_ru' => ['nullable', 'string'],
            'result_summary_uz' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'visit_files' => ['nullable', 'array'],
            'visit_files.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp,bmp,svg,pdf,doc,docx', 'max:51200'],
        ]);

        unset($validated['visit_files']);

        if (($validated['partner_organization_id'] ?? null) !== null) {
            $organizationCountryId = PartnerOrganization::query()
                ->whereKey($validated['partner_organization_id'])
                ->value('country_id');

            if ((int) $organizationCountryId !== (int) $validated['country_id']) {
                throw ValidationException::withMessages([
                    'partner_organization_id' => 'Tanlangan hamkor tashkilot tanlangan davlatga tegishli emas.',
                ]);
            }
        }

        if (($validated['responsible_department_id'] ?? null) === null && ($validated['responsible_user_id'] ?? null) !== null) {
            $validated['responsible_department_id'] = User::query()
                ->whereKey($validated['responsible_user_id'])
                ->value('department_id');
        }

        return $validated;
    }

    /**
     * @return array{countries: \Illuminate\Database\Eloquent\Collection<int, Country>, visitTypes: \Illuminate\Database\Eloquent\Collection<int, VisitType>, partnerOrganizations: \Illuminate\Database\Eloquent\Collection<int, PartnerOrganization>, responsibleUsers: \Illuminate\Database\Eloquent\Collection<int, User>, responsibleDepartments: \Illuminate\Database\Eloquent\Collection<int, Department>, directions: array<string, string>, statuses: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'visitTypes' => VisitType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'partnerOrganizations' => PartnerOrganization::query()->orderBy('name_uz')->get(['id', 'country_id', 'name_uz', 'name_ru', 'short_name']),
            'responsibleUsers' => User::query()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'department_id']),
            'responsibleDepartments' => Department::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'directions' => LocaleLabels::map(Visit::DIRECTION_TRANSLATION_KEY, Visit::DIRECTIONS),
            'statuses' => LocaleLabels::map(Visit::STATUS_TRANSLATION_KEY, Visit::STATUSES),
        ];
    }

    private function syncVisitDocuments(Request $request, Visit $visit, int $uploadedBy): void
    {
        if (! $request->hasFile('visit_files')) {
            $this->syncVisitDocumentMetadata($visit);

            return;
        }

        $visit->documents()
            ->get()
            ->each(fn (Document $document) => $document->delete());

        foreach ($request->file('visit_files', []) as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            Document::query()->create(array_merge(
                $this->visitDocumentMetadata($visit),
                $this->uploadedFilePayload($file),
                [
                    'uploaded_by' => $uploadedBy,
                    'status' => 'faol',
                    'is_confidential' => false,
                ],
            ));
        }
    }

    private function syncVisitDocumentMetadata(Visit $visit): void
    {
        $visit->documents()->update([
            'country_id' => $visit->country_id,
            'partner_organization_id' => $visit->partner_organization_id,
        ]);
    }

    private function resolveVisitDocument(Visit $visit, Document $document): Document
    {
        abort_unless($visit->documents()->whereKey($document->id)->exists(), 404);

        return $document;
    }

    /**
     * @return array<string, mixed>
     */
    private function visitDocumentMetadata(Visit $visit): array
    {
        return [
            'title_ru' => null,
            'title_uz' => null,
            'document_number' => null,
            'document_type_id' => null,
            'country_id' => $visit->country_id,
            'partner_organization_id' => $visit->partner_organization_id,
            'agreement_id' => null,
            'visit_id' => $visit->id,
            'event_id' => null,
            'description' => 'Tashrif uchun biriktirilgan fayl',
        ];
    }

    /**
     * @return array{file_name: string, file_path: string, file_ext: ?string, file_size: int, mime_type: ?string}
     */
    private function uploadedFilePayload(UploadedFile $file): array
    {
        return app(UploadedFileProcessor::class)->store(
            $file,
            self::STORAGE_DISK,
            now()->format('Y/m'),
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Country;
use App\Models\Department;
use App\Models\Document;
use App\Models\Event;
use App\Models\EventType;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EventController extends Controller implements HasMiddleware
{
    private const STORAGE_DISK = 'documents';

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view events|view own events', only: ['index', 'show']),
            new Middleware('permission:create events', only: ['create', 'store']),
            new Middleware('permission:edit events|edit own events', only: ['edit', 'update', 'destroyAttachment']),
            new Middleware('permission:delete events', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCountry = trim((string) $request->string('country_id'));
        $selectedEventType = trim((string) $request->string('event_type_id'));
        $selectedFormat = trim((string) $request->string('format'));
        $selectedStatus = trim((string) $request->string('status'));

        $eventsQuery = Event::query()->with([
            'country:id,name_uz,name_ru,iso2',
            'eventType:id,name_uz,name_ru',
            'partnerOrganization:id,name_uz,name_ru,short_name,country_id',
            'agreement:id,title_uz,title_ru,short_title_uz,short_title_ru',
            'responsibleUser:id,first_name,middle_name,last_name',
            'responsibleDepartment:id,name_uz,name_ru',
        ]);

        $this->applyOwnScope(
            $request,
            $eventsQuery,
            'view events',
            'view own events',
            function ($query, $user): void {
                $query->where(function ($eventQuery) use ($user) {
                    $eventQuery
                        ->where('responsible_user_id', $user->id)
                        ->orWhere('created_by', $user->id);
                });
            }
        );

        $events = $eventsQuery
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($eventQuery) use ($search) {
                    $eventQuery
                        ->where('title_uz', 'like', "%{$search}%")
                        ->orWhere('title_ru', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('result_summary_uz', 'like', "%{$search}%")
                        ->orWhere('result_summary_ru', 'like', "%{$search}%")
                        ->orWhereHas('country', fn ($countryQuery) => $countryQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('iso2', 'like', "%{$search}%"))
                        ->orWhereHas('eventType', fn ($eventTypeQuery) => $eventTypeQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%"))
                        ->orWhereHas('partnerOrganization', fn ($organizationQuery) => $organizationQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('short_name', 'like', "%{$search}%"))
                        ->orWhereHas('agreement', fn ($agreementQuery) => $agreementQuery
                            ->where('title_uz', 'like', "%{$search}%")
                            ->orWhere('title_ru', 'like', "%{$search}%")
                            ->orWhere('short_title_uz', 'like', "%{$search}%")
                            ->orWhere('short_title_ru', 'like', "%{$search}%"))
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
            ->when($selectedEventType !== '', fn ($query) => $query->where('event_type_id', (int) $selectedEventType))
            ->when($selectedFormat !== '', fn ($query) => $query->where('format', $selectedFormat))
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->orderByDesc('start_datetime')
            ->orderBy('title_uz')
            ->paginate(10)
            ->withQueryString();

        return view('events.index', [
            'events' => $events,
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'eventTypes' => EventType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'formats' => Event::FORMAT_LABELS,
            'statuses' => Event::STATUS_LABELS,
            'filters' => [
                'search' => $search,
                'country_id' => $selectedCountry,
                'event_type_id' => $selectedEventType,
                'format' => $selectedFormat,
                'status' => $selectedStatus,
            ],
        ]);
    }

    public function create(): View
    {
        return view('events.create', [
            'event' => new Event([
                'format' => 'offline',
                'status' => 'rejada',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request, UserNotificationService $notificationService): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        $event = DB::transaction(function () use ($request, $validated) {
            $event = Event::query()->create($validated);
            $this->syncEventDocuments($request, $event->refresh(), (int) $request->user()->id);

            return $event->refresh();
        });

        $notificationService->notifyResponsibleUser(
            $event,
            null,
            $event->responsible_user_id,
            $request->user(),
            'tadbir',
            true
        );

        return redirect()
            ->route('events.index')
            ->with('status', "Tadbir {$event->display_title} muvaffaqiyatli yaratildi.");
    }

    public function show(Request $request, Event $event): View
    {
        $this->authorizeViewedRecord(
            $request,
            $event,
            'view events',
            'view own events',
            fn (Event $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $event->load([
            'eventType:id,name_uz,name_ru',
            'country:id,name_uz,name_ru,iso2',
            'partnerOrganization:id,name_uz,name_ru,short_name',
            'agreement:id,title_uz,title_ru,short_title_uz,short_title_ru',
            'responsibleUser:id,first_name,middle_name,last_name',
            'responsibleDepartment:id,name_uz,name_ru',
            'creator:id,first_name,middle_name,last_name',
            'updater:id,first_name,middle_name,last_name',
            'documents:id,title_uz,title_ru,file_name,file_ext,file_size,file_path,mime_type,event_id,uploaded_by,created_at',
            'documents.uploader:id,first_name,middle_name,last_name',
        ]);

        return view('events.show', [
            'event' => $event,
            'formats' => Event::FORMAT_LABELS,
            'statuses' => Event::STATUS_LABELS,
        ]);
    }

    public function edit(Event $event): View
    {
        $this->authorizeOwnedRecord(
            request(),
            $event,
            'edit events',
            'edit own events',
            fn (Event $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $event->load('documents:id,title_uz,title_ru,file_name,file_ext,file_size,file_path,mime_type,event_id,uploaded_by,created_at');

        return view('events.edit', [
            'event' => $event,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Event $event, UserNotificationService $notificationService): RedirectResponse
    {
        $this->authorizeOwnedRecord(
            $request,
            $event,
            'edit events',
            'edit own events',
            fn (Event $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $previousResponsibleUserId = $event->responsible_user_id;
        $validated = $this->validatedData($request);
        $validated['updated_by'] = $request->user()?->id;

        $event = DB::transaction(function () use ($request, $event, $validated) {
            $event->update($validated);
            $this->syncEventDocuments($request, $event->refresh(), (int) $request->user()->id);

            return $event->refresh();
        });

        $notificationService->notifyResponsibleUser(
            $event,
            $previousResponsibleUserId,
            $event->responsible_user_id,
            $request->user(),
            'tadbir'
        );

        return redirect()
            ->route('events.index')
            ->with('status', "Tadbir {$event->display_title} yangilandi.");
    }

    public function destroyAttachment(Request $request, Event $event, Document $document): RedirectResponse
    {
        $this->authorizeOwnedRecord(
            $request,
            $event,
            'edit events',
            'edit own events',
            fn (Event $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $document = $this->resolveEventDocument($event, $document);
        $fileName = $document->file_name;
        $document->delete();

        return redirect()
            ->route('events.edit', $event)
            ->with('status', "Biriktirilgan fayl {$fileName} o'chirildi.");
    }

    public function destroy(Event $event): RedirectResponse
    {
        $eventTitle = $event->display_title;
        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('status', "Tadbir {$eventTitle} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title_ru' => ['required', 'string', 'max:255'],
            'title_uz' => ['required', 'string', 'max:255'],
'event_type_id' => ['nullable', 'integer', 'exists:event_types,id'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'partner_organization_id' => ['nullable', 'integer', 'exists:partner_organizations,id'],
            'agreement_id' => ['nullable', 'integer', 'exists:agreements,id'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['nullable', 'date', 'after_or_equal:start_datetime'],
            'format' => ['required', 'string', Rule::in(Event::FORMATS)],
            'status' => ['required', 'string', Rule::in(Event::STATUSES)],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'responsible_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'description' => ['nullable', 'string'],
            'result_summary_ru' => ['nullable', 'string'],
            'result_summary_uz' => ['nullable', 'string'],
'event_files' => ['nullable', 'array'],
            'event_files.*' => ['file', 'max:51200'],
        ]);

        unset($validated['event_files']);

        if (($validated['partner_organization_id'] ?? null) !== null) {
            $organizationCountryId = PartnerOrganization::query()
                ->whereKey($validated['partner_organization_id'])
                ->value('country_id');

            if ((int) $organizationCountryId !== (int) $validated['country_id']) {
                throw ValidationException::withMessages([
                    'partner_organization_id' => "Tanlangan hamkor tashkilot tanlangan davlatga tegishli emas.",
                ]);
            }
        }

        if (($validated['agreement_id'] ?? null) !== null) {
            $agreementCountryId = Agreement::query()
                ->whereKey($validated['agreement_id'])
                ->value('country_id');

            if ((int) $agreementCountryId !== (int) $validated['country_id']) {
                throw ValidationException::withMessages([
                    'agreement_id' => "Tanlangan kelishuv tanlangan davlatga tegishli emas.",
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
     * @return array{countries: \Illuminate\Database\Eloquent\Collection<int, Country>, eventTypes: \Illuminate\Database\Eloquent\Collection<int, EventType>, partnerOrganizations: \Illuminate\Database\Eloquent\Collection<int, PartnerOrganization>, agreements: \Illuminate\Database\Eloquent\Collection<int, Agreement>, responsibleUsers: \Illuminate\Database\Eloquent\Collection<int, User>, responsibleDepartments: \Illuminate\Database\Eloquent\Collection<int, Department>, formats: array<string, string>, statuses: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'eventTypes' => EventType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'partnerOrganizations' => PartnerOrganization::query()->orderBy('name_uz')->get(['id', 'country_id', 'name_uz', 'name_ru', 'short_name']),
            'agreements' => Agreement::query()->orderBy('title_uz')->get(['id', 'country_id', 'title_uz', 'title_ru', 'short_title_uz', 'short_title_ru']),
            'responsibleUsers' => User::query()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'department_id']),
            'responsibleDepartments' => Department::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'formats' => Event::FORMAT_LABELS,
            'statuses' => Event::STATUS_LABELS,
        ];
    }

    private function syncEventDocuments(Request $request, Event $event, int $uploadedBy): void
    {
        if (! $request->hasFile('event_files')) {
            $this->syncEventDocumentMetadata($event);
            return;
        }

        $event->documents()
            ->get()
            ->each(fn (Document $document) => $document->delete());

        foreach ($request->file('event_files', []) as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            Document::query()->create(array_merge(
                $this->eventDocumentMetadata($event),
                $this->uploadedFilePayload($file),
                [
                    'uploaded_by' => $uploadedBy,
                    'status' => 'faol',
                    'is_confidential' => false,
                ],
            ));
        }
    }

    private function syncEventDocumentMetadata(Event $event): void
    {
        $event->documents()->update([
            'country_id' => $event->country_id,
            'partner_organization_id' => $event->partner_organization_id,
            'agreement_id' => $event->agreement_id,
        ]);
    }

    private function resolveEventDocument(Event $event, Document $document): Document
    {
        abort_unless($event->documents()->whereKey($document->id)->exists(), 404);

        return $document;
    }

    /**
     * @return array<string, mixed>
     */
    private function eventDocumentMetadata(Event $event): array
    {
        return [
            'title_ru' => null,
            'title_uz' => null,
'document_number' => null,
            'document_type_id' => null,
            'country_id' => $event->country_id,
            'partner_organization_id' => $event->partner_organization_id,
            'agreement_id' => $event->agreement_id,
            'visit_id' => null,
            'event_id' => $event->id,
            'description' => 'Tadbir uchun biriktirilgan fayl',
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

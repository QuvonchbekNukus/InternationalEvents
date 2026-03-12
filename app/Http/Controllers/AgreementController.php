<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\AgreementDirection;
use App\Models\AgreementType;
use App\Models\Country;
use App\Models\Department;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Services\UserNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AgreementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view agreements|view own agreements', only: ['index', 'show']),
            new Middleware('permission:create agreements', only: ['create', 'store']),
            new Middleware('permission:edit agreements|edit own agreements', only: ['edit', 'update']),
            new Middleware('permission:delete agreements', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCountry = trim((string) $request->string('country_id'));
        $selectedType = trim((string) $request->string('agreement_type_id'));
        $selectedDirection = trim((string) $request->string('agreement_direction_id'));
        $selectedStatus = trim((string) $request->string('status'));

        $agreementsQuery = Agreement::query()->with([
            'country:id,name_uz,name_ru,name_cryl,iso2',
            'partnerOrganization:id,name_uz,name_ru,name_cryl,short_name,country_id',
            'agreementType:id,name_uz,name_ru,name_cryl',
            'agreementDirection:id,name_uz,name_ru,name_cryl',
            'responsibleUser:id,first_name,middle_name,last_name',
            'responsibleDepartment:id,name_uz,name_ru,name_cryl',
        ]);

        $this->applyOwnScope(
            $request,
            $agreementsQuery,
            'view agreements',
            'view own agreements',
            function ($query, $user): void {
                $query->where(function ($agreementQuery) use ($user) {
                    $agreementQuery
                        ->where('responsible_user_id', $user->id)
                        ->orWhere('created_by', $user->id);
                });
            }
        );

        $agreements = $agreementsQuery
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($agreementQuery) use ($search) {
                    $agreementQuery
                        ->where('agreement_number', 'like', "%{$search}%")
                        ->orWhere('title_uz', 'like', "%{$search}%")
                        ->orWhere('title_ru', 'like', "%{$search}%")
                        ->orWhere('title_cryl', 'like', "%{$search}%")
                        ->orWhere('short_title_uz', 'like', "%{$search}%")
                        ->orWhere('short_title_ru', 'like', "%{$search}%")
                        ->orWhere('short_title_cryl', 'like', "%{$search}%")
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
                        ->orWhereHas('agreementType', fn ($typeQuery) => $typeQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%"))
                        ->orWhereHas('agreementDirection', fn ($directionQuery) => $directionQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%"))
                        ->orWhereHas('responsibleUser', fn ($userQuery) => $userQuery
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('responsibleDepartment', fn ($departmentQuery) => $departmentQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%")
                            ->orWhere('name_cryl', 'like', "%{$search}%"));
                });
            })
            ->when($selectedCountry !== '', fn ($query) => $query->where('country_id', (int) $selectedCountry))
            ->when($selectedType !== '', fn ($query) => $query->where('agreement_type_id', (int) $selectedType))
            ->when($selectedDirection !== '', fn ($query) => $query->where('agreement_direction_id', (int) $selectedDirection))
            ->when($selectedStatus !== '', fn ($query) => $query->where('status', $selectedStatus))
            ->orderByDesc('signed_date')
            ->orderBy('title_uz')
            ->paginate(10)
            ->withQueryString();

        return view('agreements.index', [
            'agreements' => $agreements,
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'agreementTypes' => AgreementType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'agreementDirections' => AgreementDirection::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'statuses' => Agreement::STATUS_LABELS,
            'filters' => [
                'search' => $search,
                'country_id' => $selectedCountry,
                'agreement_type_id' => $selectedType,
                'agreement_direction_id' => $selectedDirection,
                'status' => $selectedStatus,
            ],
        ]);
    }

    public function create(): View
    {
        return view('agreements.create', [
            'agreement' => new Agreement([
                'status' => 'draft',
            ]),
            ...$this->formOptions(),
        ]);
    }

    public function store(Request $request, UserNotificationService $notificationService): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        $agreement = Agreement::create($validated);
        $notificationService->notifyResponsibleUser(
            $agreement,
            null,
            $agreement->responsible_user_id,
            $request->user(),
            'kelishuv',
            true
        );

        return redirect()
            ->route('agreements.index')
            ->with('status', "Kelishuv {$agreement->display_title} muvaffaqiyatli yaratildi.");
    }

    public function show(Request $request, Agreement $agreement): View
    {
        $this->authorizeViewedRecord(
            $request,
            $agreement,
            'view agreements',
            'view own agreements',
            fn (Agreement $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $agreement->load([
            'country:id,name_uz,name_ru,name_cryl,iso2',
            'partnerOrganization:id,name_uz,name_ru,name_cryl,short_name',
            'agreementType:id,name_uz,name_ru,name_cryl',
            'agreementDirection:id,name_uz,name_ru,name_cryl',
            'responsibleUser:id,first_name,middle_name,last_name',
            'responsibleDepartment:id,name_uz,name_ru,name_cryl',
            'creator:id,first_name,middle_name,last_name',
            'updater:id,first_name,middle_name,last_name',
        ]);

        return view('agreements.show', [
            'agreement' => $agreement,
            'statuses' => Agreement::STATUS_LABELS,
        ]);
    }

    public function edit(Agreement $agreement): View
    {
        $this->authorizeOwnedRecord(
            request(),
            $agreement,
            'edit agreements',
            'edit own agreements',
            fn (Agreement $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        return view('agreements.edit', [
            'agreement' => $agreement,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Agreement $agreement, UserNotificationService $notificationService): RedirectResponse
    {
        $this->authorizeOwnedRecord(
            $request,
            $agreement,
            'edit agreements',
            'edit own agreements',
            fn (Agreement $record, $user): bool => (int) $record->responsible_user_id === (int) $user->id
                || (int) $record->created_by === (int) $user->id
        );

        $previousResponsibleUserId = $agreement->responsible_user_id;
        $validated = $this->validatedData($request, $agreement);
        $validated['updated_by'] = $request->user()?->id;

        $agreement->update($validated);
        $notificationService->notifyResponsibleUser(
            $agreement->fresh(),
            $previousResponsibleUserId,
            $agreement->responsible_user_id,
            $request->user(),
            'kelishuv'
        );

        return redirect()
            ->route('agreements.index')
            ->with('status', "Kelishuv {$agreement->display_title} yangilandi.");
    }

    public function destroy(Agreement $agreement): RedirectResponse
    {
        $agreementTitle = $agreement->display_title;
        $agreement->delete();

        return redirect()
            ->route('agreements.index')
            ->with('status', "Kelishuv {$agreementTitle} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Agreement $agreement = null): array
    {
        $validated = $request->validate([
            'agreement_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('agreements', 'agreement_number')->ignore($agreement?->id),
            ],
            'title_ru' => ['required', 'string', 'max:255'],
            'title_uz' => ['required', 'string', 'max:255'],
            'title_cryl' => ['required', 'string', 'max:255'],
            'short_title_ru' => ['nullable', 'string', 'max:255'],
            'short_title_uz' => ['nullable', 'string', 'max:255'],
            'short_title_cryl' => ['nullable', 'string', 'max:255'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'partner_organization_id' => ['nullable', 'integer', 'exists:partner_organizations,id'],
            'agreement_type_id' => ['nullable', 'integer', 'exists:agreement_types,id'],
            'agreement_direction_id' => ['nullable', 'integer', 'exists:agreement_directions,id'],
            'signed_date' => ['nullable', 'date'],
            'start_date' => ['nullable', 'date', 'after_or_equal:signed_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'string', Rule::in(Agreement::STATUSES)],
            'responsible_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'responsible_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'description' => ['nullable', 'string'],
        ]);

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

        if (($validated['responsible_department_id'] ?? null) === null && ($validated['responsible_user_id'] ?? null) !== null) {
            $validated['responsible_department_id'] = User::query()
                ->whereKey($validated['responsible_user_id'])
                ->value('department_id');
        }

        return $validated;
    }

    /**
     * @return array{countries: \Illuminate\Database\Eloquent\Collection<int, Country>, partnerOrganizations: \Illuminate\Database\Eloquent\Collection<int, PartnerOrganization>, agreementTypes: \Illuminate\Database\Eloquent\Collection<int, AgreementType>, agreementDirections: \Illuminate\Database\Eloquent\Collection<int, AgreementDirection>, responsibleUsers: \Illuminate\Database\Eloquent\Collection<int, User>, responsibleDepartments: \Illuminate\Database\Eloquent\Collection<int, Department>, statuses: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'partnerOrganizations' => PartnerOrganization::query()->orderBy('name_uz')->get(['id', 'country_id', 'name_uz', 'name_ru', 'name_cryl', 'short_name']),
            'agreementTypes' => AgreementType::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'agreementDirections' => AgreementDirection::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'responsibleUsers' => User::query()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'department_id']),
            'responsibleDepartments' => Department::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru', 'name_cryl']),
            'statuses' => Agreement::STATUS_LABELS,
        ];
    }
}

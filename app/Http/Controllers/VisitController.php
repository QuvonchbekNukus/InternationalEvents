<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Department;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VisitController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view visits', only: ['index']),
            new Middleware('permission:create visits', only: ['create', 'store']),
            new Middleware('permission:edit visits', only: ['edit', 'update']),
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

        $visits = Visit::query()
            ->with([
                'country:id,name_uz,name_ru,iso2',
                'visitType:id,name_uz',
                'partnerOrganization:id,name_uz,name_ru,short_name,country_id',
                'responsibleUser:id,first_name,middle_name,last_name',
                'responsibleDepartment:id,name_uz',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($visitQuery) use ($search) {
                    $visitQuery
                        ->where('title_uz', 'like', "%{$search}%")
                        ->orWhere('title_ru', 'like', "%{$search}%")
                        ->orWhere('title_cryl', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('purpose_uz', 'like', "%{$search}%")
                        ->orWhere('purpose_ru', 'like', "%{$search}%")
                        ->orWhere('purpose_cryl', 'like', "%{$search}%")
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
            'visitTypes' => VisitType::query()->orderBy('name_uz')->get(['id', 'name_uz']),
            'directions' => Visit::DIRECTION_LABELS,
            'statuses' => Visit::STATUS_LABELS,
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        $visit = Visit::create($validated);

        return redirect()
            ->route('visits.index')
            ->with('status', "Tashrif {$visit->display_title} muvaffaqiyatli yaratildi.");
    }

    public function edit(Visit $visit): View
    {
        return view('visits.edit', [
            'visit' => $visit,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, Visit $visit): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $validated['updated_by'] = $request->user()?->id;

        $visit->update($validated);

        return redirect()
            ->route('visits.index')
            ->with('status', "Tashrif {$visit->display_title} yangilandi.");
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
            'title_cryl' => ['required', 'string', 'max:255'],
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
            'purpose_cryl' => ['nullable', 'string'],
            'result_summary_ru' => ['nullable', 'string'],
            'result_summary_uz' => ['nullable', 'string'],
            'result_summary_cryl' => ['nullable', 'string'],
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
     * @return array{countries: \Illuminate\Database\Eloquent\Collection<int, Country>, visitTypes: \Illuminate\Database\Eloquent\Collection<int, VisitType>, partnerOrganizations: \Illuminate\Database\Eloquent\Collection<int, PartnerOrganization>, responsibleUsers: \Illuminate\Database\Eloquent\Collection<int, User>, responsibleDepartments: \Illuminate\Database\Eloquent\Collection<int, Department>, directions: array<string, string>, statuses: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'visitTypes' => VisitType::query()->orderBy('name_uz')->get(['id', 'name_uz']),
            'partnerOrganizations' => PartnerOrganization::query()->orderBy('name_uz')->get(['id', 'country_id', 'name_uz', 'name_ru', 'short_name']),
            'responsibleUsers' => User::query()->orderBy('last_name')->orderBy('first_name')->get(['id', 'first_name', 'middle_name', 'last_name', 'department_id']),
            'responsibleDepartments' => Department::query()->orderBy('name_uz')->get(['id', 'name_uz']),
            'directions' => Visit::DIRECTION_LABELS,
            'statuses' => Visit::STATUS_LABELS,
        ];
    }
}

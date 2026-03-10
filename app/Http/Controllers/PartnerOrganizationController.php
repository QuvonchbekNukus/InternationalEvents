<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\OrganizationType;
use App\Models\PartnerOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartnerOrganizationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view partner organizations', only: ['index']),
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
            ->with(['country:id,name_uz,name_ru,iso2', 'organizationType:id,name_uz'])
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
                            ->orWhere('iso2', 'like', "%{$search}%"))
                        ->orWhereHas('organizationType', fn ($typeQuery) => $typeQuery
                            ->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%"));
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
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'organizationTypes' => OrganizationType::query()->orderBy('name_uz')->get(['id', 'name_uz']),
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

    public function store(Request $request): RedirectResponse
    {
        $partnerOrganization = PartnerOrganization::create($this->validatedData($request));

        return redirect()
            ->route('partner-organizations.index')
            ->with('status', "Hamkor tashkilot {$partnerOrganization->display_name} muvaffaqiyatli yaratildi.");
    }

    public function edit(PartnerOrganization $partnerOrganization): View
    {
        return view('partner-organizations.edit', [
            'partnerOrganization' => $partnerOrganization,
            ...$this->formOptions(),
        ]);
    }

    public function update(Request $request, PartnerOrganization $partnerOrganization): RedirectResponse
    {
        $partnerOrganization->update($this->validatedData($request));

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
        $partnerOrganization->delete();

        return redirect()
            ->route('partner-organizations.index')
            ->with('status', "Hamkor tashkilot {$partnerOrganizationName} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
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
        ]);
    }

    /**
     * @return array{countries: \Illuminate\Database\Eloquent\Collection<int, Country>, organizationTypes: \Illuminate\Database\Eloquent\Collection<int, OrganizationType>, statuses: array<string, string>}
     */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->orderBy('name_uz')->get(['id', 'name_uz', 'name_ru']),
            'organizationTypes' => OrganizationType::query()->orderBy('name_uz')->get(['id', 'name_uz']),
            'statuses' => PartnerOrganization::STATUS_LABELS,
        ];
    }
}

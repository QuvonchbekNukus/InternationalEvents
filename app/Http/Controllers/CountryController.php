<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Support\LocaleLabels;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CountryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view countries', only: ['index', 'show']),
            new Middleware('permission:create countries', only: ['create', 'store']),
            new Middleware('permission:edit countries', only: ['edit', 'update']),
            new Middleware('permission:delete countries', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedStatus = trim((string) $request->string('status'));

        $countries = Country::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($countryQuery) use ($search) {
                    $countryQuery
                        ->where('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_uz', 'like', "%{$search}%")
                        ->orWhere('iso2', 'like', "%{$search}%")
                        ->orWhere('iso3', 'like', "%{$search}%")
                        ->orWhere('region_ru', 'like', "%{$search}%")
                        ->orWhere('region_uz', 'like', "%{$search}%")
                        ->orWhere('partnership_history', 'like', "%{$search}%");
                });
            })
            ->when($selectedStatus !== '', fn ($query) => $query->where('cooperation_status', $selectedStatus))
            ->orderByRaw('coalesce(name_uz, name_ru) asc')
            ->paginate(10)
            ->withQueryString();

        return view('countries.index', [
            'countries' => $countries,
            'filters' => [
                'search' => $search,
                'status' => $selectedStatus,
            ],
            'statuses' => LocaleLabels::map(Country::STATUS_TRANSLATION_KEY, Country::STATUSES),
        ]);
    }

    public function create(): View
    {
        return view('countries.create', [
            'country' => new Country([
                'cooperation_status' => 'faol',
            ]),
            'statuses' => LocaleLabels::map(Country::STATUS_TRANSLATION_KEY, Country::STATUSES),
        ]);
    }

    public function show(Request $request, Country $country): View
    {
        $country->loadMissing([]);

        $canViewPartnerOrganizations = (bool) $request->user()?->can('view partner organizations');
        $canViewAgreements = (bool) $request->user()?->can('view agreements')
            || (bool) $request->user()?->can('view own agreements');
        $canViewVisits = (bool) $request->user()?->can('view visits')
            || (bool) $request->user()?->can('view own visits');
        $canViewEvents = (bool) $request->user()?->can('view events')
            || (bool) $request->user()?->can('view own events');
        $canViewDocuments = (bool) $request->user()?->can('view documents')
            || (bool) $request->user()?->can('view own documents');

        $partnerOrganizations = $canViewPartnerOrganizations
            ? $country->partnerOrganizations()
                ->with(['organizationType:id,name_uz,name_ru'])
                ->withCount('partnerContacts')
                ->orderByRaw('coalesce(name_uz, name_ru) asc')
                ->get()
            : collect();

        return view('countries.show', [
            'country' => $country,
            'statuses' => LocaleLabels::map(Country::STATUS_TRANSLATION_KEY, Country::STATUSES),
            'partnerOrganizations' => $partnerOrganizations,
            'agreements' => $this->visibleAgreements($request, $country),
            'visits' => $this->visibleVisits($request, $country),
            'events' => $this->visibleEvents($request, $country),
            'documents' => $this->visibleDocuments($request, $country),
            'relatedAccess' => [
                'partner_organizations' => $canViewPartnerOrganizations,
                'agreements' => $canViewAgreements,
                'visits' => $canViewVisits,
                'events' => $canViewEvents,
                'documents' => $canViewDocuments,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $country = Country::create($this->validatedData($request));

        return redirect()
            ->route('countries.index')
            ->with('status', "Davlat {$country->display_name} muvaffaqiyatli yaratildi.");
    }

    public function edit(Country $country): View
    {
        return view('countries.edit', [
            'country' => $country,
            'statuses' => LocaleLabels::map(Country::STATUS_TRANSLATION_KEY, Country::STATUSES),
        ]);
    }

    public function update(Request $request, Country $country): RedirectResponse
    {
        $country->update($this->validatedData($request, $country));

        return redirect()
            ->route('countries.index')
            ->with('status', "Davlat {$country->display_name} yangilandi.");
    }

    public function destroy(Country $country): RedirectResponse
    {
        if ($country->partnerOrganizations()->exists()) {
            return back()->with('error', "Davlatga hamkor tashkilotlar biriktirilgan. Avval ularni boshqa davlatga o'tkazing yoki o'chiring.");
        }

        if ($country->agreements()->exists()) {
            return back()->with('error', "Davlatga kelishuvlar biriktirilgan. Avval ularni boshqa davlatga o'tkazing.");
        }

        if ($country->visits()->exists()) {
            return back()->with('error', "Davlatga tashriflar biriktirilgan. Avval ularni boshqa davlatga o'tkazing.");
        }

        if ($country->events()->exists()) {
            return back()->with('error', "Davlatga tadbirlar biriktirilgan. Avval ularni boshqa davlatga o'tkazing.");
        }

        $countryName = $country->display_name;
        $country->delete();

        return redirect()
            ->route('countries.index')
            ->with('status', "Davlat {$countryName} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Country $country = null): array
    {
        $validated = $request->validate([
            'name_ru' => ['required', 'string', 'max:255'],
            'name_uz' => ['nullable', 'string', 'max:255'],
            'iso2' => ['nullable', 'string', 'size:2', Rule::unique('countries', 'iso2')->ignore($country?->id)],
            'iso3' => ['nullable', 'string', 'size:3', Rule::unique('countries', 'iso3')->ignore($country?->id)],
            'region_ru' => ['nullable', 'string', 'max:255'],
            'region_uz' => ['nullable', 'string', 'max:255'],
            'cooperation_status' => ['required', 'string', Rule::in(Country::STATUSES)],
            'boundary_geojson_path' => ['nullable', 'string', 'max:255'],
            'partnership_history' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['iso2'] = isset($validated['iso2']) ? strtoupper((string) $validated['iso2']) : null;
        $validated['iso3'] = isset($validated['iso3']) ? strtoupper((string) $validated['iso3']) : null;
        $validated['flag_path'] = null;

        return $validated;
    }

    private function visibleAgreements(Request $request, Country $country)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view agreements') && ! $user->can('view own agreements'))) {
            return collect();
        }

        $query = $country->agreements()->with([
            'partnerOrganization:id,name_uz,name_ru,short_name',
            'agreementType:id,name_uz,name_ru',
            'agreementDirection:id,name_uz,name_ru',
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
            ->orderByRaw('coalesce(title_uz, title_ru) asc')
            ->get();
    }

    private function visibleVisits(Request $request, Country $country)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view visits') && ! $user->can('view own visits'))) {
            return collect();
        }

        $query = $country->visits()->with([
            'partnerOrganization:id,name_uz,name_ru,short_name',
            'visitType:id,name_uz,name_ru',
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
            ->orderByRaw('coalesce(title_uz, title_ru) asc')
            ->get();
    }

    private function visibleEvents(Request $request, Country $country)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view events') && ! $user->can('view own events'))) {
            return collect();
        }

        $query = $country->events()->with([
            'partnerOrganization:id,name_uz,name_ru,short_name',
            'eventType:id,name_uz,name_ru',
            'agreement:id,title_uz,title_ru,short_title_uz,short_title_ru',
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
            ->orderByRaw('coalesce(title_uz, title_ru) asc')
            ->get();
    }

    private function visibleDocuments(Request $request, Country $country)
    {
        $user = $request->user();

        if (! $user || (! $user->can('view documents') && ! $user->can('view own documents'))) {
            return collect();
        }

        $query = $country->documents()->with([
            'documentType:id,name_uz,name_ru',
            'partnerOrganization:id,name_uz,name_ru,short_name',
            'agreement:id,title_uz,title_ru,short_title_uz,short_title_ru',
            'visit:id,title_uz,title_ru',
            'event:id,title_uz,title_ru',
            'uploader:id,first_name,middle_name,last_name',
        ]);

        if (! $user->can('view documents') && $user->can('view own documents')) {
            $query->where('uploaded_by', $user->id);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByRaw('coalesce(title_uz, title_ru,  file_name) asc')
            ->get();
    }
}

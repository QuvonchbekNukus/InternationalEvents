<?php

namespace App\Http\Controllers;

use App\Models\Country;
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
            new Middleware('permission:view countries', only: ['index']),
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
                        ->orWhere('name_cryl', 'like', "%{$search}%")
                        ->orWhere('iso2', 'like', "%{$search}%")
                        ->orWhere('iso3', 'like', "%{$search}%")
                        ->orWhere('region_ru', 'like', "%{$search}%")
                        ->orWhere('region_uz', 'like', "%{$search}%")
                        ->orWhere('region_cryl', 'like', "%{$search}%");
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
            'statuses' => Country::STATUS_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('countries.create', [
            'country' => new Country([
                'cooperation_status' => 'active',
            ]),
            'statuses' => Country::STATUS_LABELS,
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
            'statuses' => Country::STATUS_LABELS,
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
            'name_cryl' => ['nullable', 'string', 'max:255'],
            'iso2' => ['nullable', 'string', 'size:2', Rule::unique('countries', 'iso2')->ignore($country?->id)],
            'iso3' => ['nullable', 'string', 'size:3', Rule::unique('countries', 'iso3')->ignore($country?->id)],
            'region_ru' => ['nullable', 'string', 'max:255'],
            'region_uz' => ['nullable', 'string', 'max:255'],
            'region_cryl' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'default_zoom' => ['nullable', 'numeric', 'between:1,20'],
            'cooperation_status' => ['required', 'string', Rule::in(Country::STATUSES)],
            'boundary_geojson_path' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['iso2'] = isset($validated['iso2']) ? strtoupper((string) $validated['iso2']) : null;
        $validated['iso3'] = isset($validated['iso3']) ? strtoupper((string) $validated['iso3']) : null;
        $validated['flag_path'] = null;

        return $validated;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PartnerContactController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view partner contacts', only: ['index']),
            new Middleware('permission:create partner contacts', only: ['create', 'store']),
            new Middleware('permission:edit partner contacts', only: ['edit', 'update']),
            new Middleware('permission:delete partner contacts', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedOrganization = trim((string) $request->string('partner_organization_id'));
        $selectedPrimary = trim((string) $request->string('primary'));

        $partnerContacts = PartnerContact::query()
            ->with(['partnerOrganization:id,name_uz,name_ru,name_cryl,short_name,country_id', 'partnerOrganization.country:id,name_uz,name_ru,name_cryl,iso2'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($partnerContactQuery) use ($search) {
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);

        $partnerContact = DB::transaction(function () use ($validated) {
            $partnerContact = PartnerContact::query()->create($validated);
            $this->syncPrimaryContact($partnerContact);

            return $partnerContact;
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

        DB::transaction(function () use ($partnerContact, $validated) {
            $partnerContact->update($validated);
            $this->syncPrimaryContact($partnerContact->refresh());
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
            'position_ru' => ['nullable', 'string', 'max:255'],
            'position_uz' => ['nullable', 'string', 'max:255'],
            'position_cryl' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');

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
}

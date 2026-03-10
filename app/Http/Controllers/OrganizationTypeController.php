<?php

namespace App\Http\Controllers;

use App\Models\OrganizationType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class OrganizationTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view organization types', only: ['index']),
            new Middleware('permission:create organization types', only: ['create', 'store']),
            new Middleware('permission:edit organization types', only: ['edit', 'update']),
            new Middleware('permission:delete organization types', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $organizationTypes = OrganizationType::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($organizationTypeQuery) use ($search) {
                    $organizationTypeQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('organization-types.index', [
            'organizationTypes' => $organizationTypes,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('organization-types.create', [
            'organizationType' => new OrganizationType(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organizationType = OrganizationType::create($this->validatedData($request));

        return redirect()
            ->route('organization-types.index')
            ->with('status', "Tashkilot turi {$organizationType->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(OrganizationType $organizationType): View
    {
        return view('organization-types.edit', [
            'organizationType' => $organizationType,
        ]);
    }

    public function update(Request $request, OrganizationType $organizationType): RedirectResponse
    {
        $organizationType->update($this->validatedData($request));

        return redirect()
            ->route('organization-types.index')
            ->with('status', "Tashkilot turi {$organizationType->name_uz} yangilandi.");
    }

    public function destroy(OrganizationType $organizationType): RedirectResponse
    {
        $organizationTypeName = $organizationType->name_uz;
        $organizationType->delete();

        return redirect()
            ->route('organization-types.index')
            ->with('status', "Tashkilot turi {$organizationTypeName} o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name_ru' => ['required', 'string', 'max:255'],
            'name_uz' => ['required', 'string', 'max:255'],
            'name_cryl' => ['required', 'string', 'max:255'],
        ]);
    }
}

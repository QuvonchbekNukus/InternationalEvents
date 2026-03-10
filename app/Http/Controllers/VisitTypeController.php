<?php

namespace App\Http\Controllers;

use App\Models\VisitType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class VisitTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view visit types', only: ['index']),
            new Middleware('permission:create visit types', only: ['create', 'store']),
            new Middleware('permission:edit visit types', only: ['edit', 'update']),
            new Middleware('permission:delete visit types', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $visitTypes = VisitType::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($visitTypeQuery) use ($search) {
                    $visitTypeQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('visit-types.index', [
            'visitTypes' => $visitTypes,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('visit-types.create', [
            'visitType' => new VisitType(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $visitType = VisitType::create($this->validatedData($request));

        return redirect()
            ->route('visit-types.index')
            ->with('status', "Tashrif turi {$visitType->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(VisitType $visitType): View
    {
        return view('visit-types.edit', [
            'visitType' => $visitType,
        ]);
    }

    public function update(Request $request, VisitType $visitType): RedirectResponse
    {
        $visitType->update($this->validatedData($request));

        return redirect()
            ->route('visit-types.index')
            ->with('status', "Tashrif turi {$visitType->name_uz} yangilandi.");
    }

    public function destroy(VisitType $visitType): RedirectResponse
    {
        $visitTypeName = $visitType->name_uz;
        $visitType->delete();

        return redirect()
            ->route('visit-types.index')
            ->with('status', "Tashrif turi {$visitTypeName} o'chirildi.");
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

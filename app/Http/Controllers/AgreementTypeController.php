<?php

namespace App\Http\Controllers;

use App\Models\AgreementType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class AgreementTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view agreement types', only: ['index']),
            new Middleware('permission:create agreement types', only: ['create', 'store']),
            new Middleware('permission:edit agreement types', only: ['edit', 'update']),
            new Middleware('permission:delete agreement types', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $agreementTypes = AgreementType::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($agreementTypeQuery) use ($search) {
                    $agreementTypeQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('agreement-types.index', [
            'agreementTypes' => $agreementTypes,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('agreement-types.create', [
            'agreementType' => new AgreementType(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $agreementType = AgreementType::create($this->validatedData($request));

        return redirect()
            ->route('agreement-types.index')
            ->with('status', "Kelishuv turi {$agreementType->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(AgreementType $agreementType): View
    {
        return view('agreement-types.edit', [
            'agreementType' => $agreementType,
        ]);
    }

    public function update(Request $request, AgreementType $agreementType): RedirectResponse
    {
        $agreementType->update($this->validatedData($request));

        return redirect()
            ->route('agreement-types.index')
            ->with('status', "Kelishuv turi {$agreementType->name_uz} yangilandi.");
    }

    public function destroy(AgreementType $agreementType): RedirectResponse
    {
        $agreementTypeName = $agreementType->name_uz;
        $agreementType->delete();

        return redirect()
            ->route('agreement-types.index')
            ->with('status', "Kelishuv turi {$agreementTypeName} o'chirildi.");
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

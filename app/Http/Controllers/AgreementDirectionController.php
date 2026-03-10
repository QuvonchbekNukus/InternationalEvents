<?php

namespace App\Http\Controllers;

use App\Models\AgreementDirection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class AgreementDirectionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view agreement directions', only: ['index']),
            new Middleware('permission:create agreement directions', only: ['create', 'store']),
            new Middleware('permission:edit agreement directions', only: ['edit', 'update']),
            new Middleware('permission:delete agreement directions', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $agreementDirections = AgreementDirection::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($agreementDirectionQuery) use ($search) {
                    $agreementDirectionQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('agreement-directions.index', [
            'agreementDirections' => $agreementDirections,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('agreement-directions.create', [
            'agreementDirection' => new AgreementDirection(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $agreementDirection = AgreementDirection::create($this->validatedData($request));

        return redirect()
            ->route('agreement-directions.index')
            ->with('status', "Kelishuv yo'nalishi {$agreementDirection->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(AgreementDirection $agreementDirection): View
    {
        return view('agreement-directions.edit', [
            'agreementDirection' => $agreementDirection,
        ]);
    }

    public function update(Request $request, AgreementDirection $agreementDirection): RedirectResponse
    {
        $agreementDirection->update($this->validatedData($request));

        return redirect()
            ->route('agreement-directions.index')
            ->with('status', "Kelishuv yo'nalishi {$agreementDirection->name_uz} yangilandi.");
    }

    public function destroy(AgreementDirection $agreementDirection): RedirectResponse
    {
        $agreementDirectionName = $agreementDirection->name_uz;
        $agreementDirection->delete();

        return redirect()
            ->route('agreement-directions.index')
            ->with('status', "Kelishuv yo'nalishi {$agreementDirectionName} o'chirildi.");
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

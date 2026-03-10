<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class RankController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view ranks', only: ['index']),
            new Middleware('permission:create ranks', only: ['create', 'store']),
            new Middleware('permission:edit ranks', only: ['edit', 'update']),
            new Middleware('permission:delete ranks', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $ranks = Rank::query()
            ->withCount('users')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($rankQuery) use ($search) {
                    $rankQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('ranks.index', [
            'ranks' => $ranks,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('ranks.create', [
            'rank' => new Rank(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rank = Rank::create($this->validatedData($request));

        return redirect()
            ->route('ranks.index')
            ->with('status', "Unvon {$rank->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(Rank $rank): View
    {
        return view('ranks.edit', [
            'rank' => $rank,
        ]);
    }

    public function update(Request $request, Rank $rank): RedirectResponse
    {
        $rank->update($this->validatedData($request));

        return redirect()
            ->route('ranks.index')
            ->with('status', "Unvon {$rank->name_uz} yangilandi.");
    }

    public function destroy(Rank $rank): RedirectResponse
    {
        if ($rank->users()->exists()) {
            return back()->with('error', "Unvonga foydalanuvchilar biriktirilgan. Avval ulardagi unvonni yangilang.");
        }

        $rankName = $rank->name_uz;
        $rank->delete();

        return redirect()
            ->route('ranks.index')
            ->with('status', "Unvon {$rankName} o'chirildi.");
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

<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class EventTypeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view event types', only: ['index']),
            new Middleware('permission:create event types', only: ['create', 'store']),
            new Middleware('permission:edit event types', only: ['edit', 'update']),
            new Middleware('permission:delete event types', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $eventTypes = EventType::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($eventTypeQuery) use ($search) {
                    $eventTypeQuery
                        ->where('name_uz', 'like', "%{$search}%")
                        ->orWhere('name_ru', 'like', "%{$search}%")
                        ->orWhere('name_cryl', 'like', "%{$search}%");
                });
            })
            ->orderBy('name_uz')
            ->paginate(10)
            ->withQueryString();

        return view('event-types.index', [
            'eventTypes' => $eventTypes,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('event-types.create', [
            'eventType' => new EventType(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $eventType = EventType::create($this->validatedData($request));

        return redirect()
            ->route('event-types.index')
            ->with('status', "Tadbir turi {$eventType->name_uz} muvaffaqiyatli yaratildi.");
    }

    public function edit(EventType $eventType): View
    {
        return view('event-types.edit', [
            'eventType' => $eventType,
        ]);
    }

    public function update(Request $request, EventType $eventType): RedirectResponse
    {
        $eventType->update($this->validatedData($request));

        return redirect()
            ->route('event-types.index')
            ->with('status', "Tadbir turi {$eventType->name_uz} yangilandi.");
    }

    public function destroy(EventType $eventType): RedirectResponse
    {
        $eventTypeName = $eventType->name_uz;
        $eventType->delete();

        return redirect()
            ->route('event-types.index')
            ->with('status', "Tadbir turi {$eventTypeName} o'chirildi.");
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

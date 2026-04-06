<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view activity logs', only: ['index']),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCauser = trim((string) $request->string('causer_id'));
        $selectedEvent = trim((string) $request->string('event'));
        $selectedSubjectType = trim((string) $request->string('subject_type'));
        $fromDate = trim((string) $request->string('from'));
        $toDate = trim((string) $request->string('to'));

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($activityQuery) use ($search) {
                    $activityQuery
                        ->where('description', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('properties->subject_label', 'like', "%{$search}%")
                        ->orWhere('properties->subject_type_label', 'like', "%{$search}%")
                        ->orWhere('properties->causer_name', 'like', "%{$search}%")
                        ->orWhere('properties->ip_address', 'like', "%{$search}%")
                        ->orWhere('properties->file_name', 'like', "%{$search}%")
                        ->orWhere('properties->route', 'like', "%{$search}%")
                        ->orWhere('properties->phone_masked', 'like', "%{$search}%")
                        ->orWhere('properties->roles', 'like', "%{$search}%")
                        ->orWhere('properties->permissions', 'like', "%{$search}%");
                });
            })
            ->when($selectedCauser !== '', function ($query) use ($selectedCauser) {
                $query
                    ->where('causer_type', User::class)
                    ->where('causer_id', (int) $selectedCauser);
            })
            ->when($selectedEvent !== '', fn ($query) => $query->where('event', $selectedEvent))
            ->when($selectedSubjectType !== '', fn ($query) => $query->where('subject_type', $selectedSubjectType))
            ->when($fromDate !== '', fn ($query) => $query->whereDate('created_at', '>=', $fromDate))
            ->when($toDate !== '', fn ($query) => $query->whereDate('created_at', '<=', $toDate))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $eventLabels = trans('ui.activity_log.events');
        $subjectTypeLabels = trans('ui.activity_log.subject_types');

        return view('activity-logs.index', [
            'activities' => $activities,
            'users' => User::query()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'middle_name', 'last_name']),
            'eventLabels' => is_array($eventLabels) ? $eventLabels : [],
            'subjectTypeLabels' => is_array($subjectTypeLabels) ? $subjectTypeLabels : [],
            'filters' => [
                'search' => $search,
                'causer_id' => $selectedCauser,
                'event' => $selectedEvent,
                'subject_type' => $selectedSubjectType,
                'from' => $fromDate,
                'to' => $toDate,
            ],
        ]);
    }
}

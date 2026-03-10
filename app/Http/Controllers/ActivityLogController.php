<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\AgreementDirection;
use App\Models\AgreementType;
use App\Models\Country;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Event;
use App\Models\EventType;
use App\Models\OrganizationType;
use App\Models\PartnerContact;
use App\Models\PartnerOrganization;
use App\Models\Rank;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller implements HasMiddleware
{
    private const EVENT_LABELS = [
        'login' => 'Tizimga kirdi',
        'logout' => 'Tizimdan chiqdi',
        'created' => 'Yaratildi',
        'updated' => 'Tahrirlandi',
        'deleted' => "O'chirildi",
        'downloaded' => 'Yuklab olindi',
    ];

    private const SUBJECT_TYPE_LABELS = [
        User::class => 'Foydalanuvchi',
        Department::class => "Bo'lim",
        Rank::class => 'Unvon',
        Country::class => 'Davlat',
        PartnerOrganization::class => 'Hamkor tashkilot',
        PartnerContact::class => 'Hamkor kontakt',
        OrganizationType::class => 'Tashkilot turi',
        Agreement::class => 'Kelishuv',
        AgreementType::class => 'Kelishuv turi',
        AgreementDirection::class => "Kelishuv yo'nalishi",
        Visit::class => 'Tashrif',
        VisitType::class => 'Tashrif turi',
        Event::class => 'Tadbir',
        EventType::class => 'Tadbir turi',
        Document::class => 'Hujjat',
        DocumentType::class => 'Hujjat turi',
    ];

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
                        ->orWhere('properties->file_name', 'like', "%{$search}%");
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

        return view('activity-logs.index', [
            'activities' => $activities,
            'users' => User::query()
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'middle_name', 'last_name']),
            'eventLabels' => self::EVENT_LABELS,
            'subjectTypeLabels' => self::SUBJECT_TYPE_LABELS,
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

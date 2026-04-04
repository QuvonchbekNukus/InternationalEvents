<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PartnerContact;
use App\Models\Visit;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const DEFAULT_WEEKDAY_LABELS = ['Du', 'Se', 'Cho', 'Pay', 'Ju', 'Sha', 'Yak'];

    private const DEFAULT_WEEKDAY_NAMES = [
        1 => 'Dushanba',
        2 => 'Seshanba',
        3 => 'Chorshanba',
        4 => 'Payshanba',
        5 => 'Juma',
        6 => 'Shanba',
        7 => 'Yakshanba',
    ];

    private const DEFAULT_MONTH_LABELS = [
        1 => 'Yanvar',
        2 => 'Fevral',
        3 => 'Mart',
        4 => 'Aprel',
        5 => 'May',
        6 => 'Iyun',
        7 => 'Iyul',
        8 => 'Avgust',
        9 => 'Sentyabr',
        10 => 'Oktyabr',
        11 => 'Noyabr',
        12 => 'Dekabr',
    ];

    private const DISPLAY_EVENT_STATUSES = ['rejada', 'hozirda', 'tugatilgan'];

    private const DISPLAY_VISIT_STATUSES = ['planned', 'ongoing', 'completed'];

    private const CALENDAR_FILTER_GROUP_TYPE = 'type';

    private const CALENDAR_FILTER_GROUP_STATUS = 'status';

    private const CALENDAR_ITEM_TYPE_EVENT = 'event';

    private const CALENDAR_ITEM_TYPE_VISIT = 'visit';

    private const CALENDAR_ITEM_TYPE_BIRTHDAY = 'birthday';

    private const CALENDAR_STATUS_PLANNED = 'planned';

    private const CALENDAR_STATUS_ONGOING = 'ongoing';

    private const CALENDAR_STATUS_COMPLETED = 'completed';

    private const CALENDAR_RECURRENCE_YEARLY = 'yearly';

    /** Kun kataklarida boshlanish sanasi bo‘yicha ko‘rinadigan markerlar (doiracha) soni. */
    private const CALENDAR_DAY_MARKER_LIMIT = 14;

    public function __invoke(Request $request): View
    {
        return view('dashboard', $this->buildDashboardCalendarViewData($request));
    }

    public function calendar(Request $request): JsonResponse
    {
        return response()->json($this->buildDashboardCalendarViewData($request));
    }

    private function buildDashboardCalendarViewData(Request $request): array
    {
        $eventCalendar = $this->buildEventCalendar($request);

        return [
            'eventCalendar' => $eventCalendar,
            'calendarMonthOptions' => $this->resolveCalendarMonthOptions(),
            'calendarYearOptions' => $this->resolveCalendarYearOptions($eventCalendar['month_key'] ?? null),
            'calendarDataUrl' => route('dashboard.calendar'),
        ];
    }

    private function buildEventCalendar(Request $request): array
    {
        $month = $this->resolveMonth($request);
        $monthStart = $month->startOfMonth();
        $monthEnd = $month->endOfMonth();
        $calendarStart = $monthStart->startOfWeek(CarbonImmutable::MONDAY);
        $calendarEnd = $monthEnd->endOfWeek(CarbonImmutable::SUNDAY);
        $texts = $this->resolveCalendarTexts();

        $calendar = [
            'has_access' => false,
            'month_key' => $monthStart->format('Y-m'),
            'month_label' => $this->formatMonthLabel($monthStart),
            'subtitle' => $texts['subtitle'],
            'day_labels' => $this->resolveWeekdayLabels(),
            'prev_url' => route('dashboard', ['month' => $monthStart->subMonth()->format('Y-m')]),
            'next_url' => route('dashboard', ['month' => $monthStart->addMonth()->format('Y-m')]),
            'listing_url' => null,
            'listing_label' => $texts['all_events'],
            'count_label' => $this->replaceCountPlaceholder($texts['item_count'], 0),
            'item_count' => 0,
            'items' => [],
            'summary' => $this->buildCalendarSummary(collect(), $texts),
            'filters' => $this->buildCalendarFilters(collect(), $texts),
            'weeks' => [],
            'day_lookup' => [],
            'selected_date' => null,
            'selected_day' => null,
            'texts' => $texts,
        ];

        $user = $request->user();
        $canViewEvents = (bool) $user?->can('view events') || (bool) $user?->can('view own events');
        $canViewVisits = (bool) $user?->can('view visits') || (bool) $user?->can('view own visits');
        $canViewPartnerContacts = (bool) $user?->can('view partner contacts');

        if (! $canViewEvents && ! $canViewVisits && ! $canViewPartnerContacts) {
            return $calendar;
        }

        $calendar['has_access'] = true;
        $calendar['listing_url'] = $canViewEvents
            ? route('events.index')
            : ($canViewVisits ? route('visits.index') : route('partner-contacts.index'));
        $calendar['listing_label'] = $canViewEvents
            ? $texts['all_events']
            : ($canViewVisits ? $texts['all_visits'] : $texts['all_contacts']);

        $items = collect();

        if ($canViewEvents) {
            $items = $items->concat($this->fetchEventCalendarItems($request, $calendarStart, $calendarEnd, $texts));
        }

        if ($canViewVisits) {
            $items = $items->concat($this->fetchVisitCalendarItems($request, $calendarStart, $calendarEnd, $texts));
        }

        if ($canViewPartnerContacts) {
            $items = $items->concat($this->fetchBirthdayCalendarItems($calendarStart, $calendarEnd, $texts));
        }

        $items = $items
            ->sort(fn (array $left, array $right): int => $this->compareCalendarItems($left, $right))
            ->values();

        $calendar['item_count'] = $items->count();
        $calendar['items'] = $items
            ->map(fn (array $item): array => $this->exportCalendarItem($item))
            ->values()
            ->all();
        $calendar['count_label'] = $this->replaceCountPlaceholder($texts['item_count'], $items->count());
        $calendar['summary'] = $this->buildCalendarSummary($items, $texts);
        $calendar['filters'] = $this->buildCalendarFilters($items, $texts);

        $dayLookup = $this->buildCalendarDayLookup($calendarStart, $calendarEnd, $monthStart, $items, $texts);
        $selectedDate = $this->resolveSelectedCalendarDate($dayLookup, $monthStart, $monthEnd);

        for ($weekStart = $calendarStart; $weekStart->lte($calendarEnd); $weekStart = $weekStart->addWeek()) {
            $weekEnd = $weekStart->addDays(6)->startOfDay();
            $days = [];

            for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
                $day = $weekStart->addDays($dayOffset);
                $dayData = $dayLookup[$day->toDateString()];

                $days[] = [
                    'date' => $dayData['date'],
                    'day_number' => $day->day,
                    'label' => $dayData['label'],
                    'is_current_month' => $dayData['is_current_month'],
                    'is_today' => $dayData['is_today'],
                    'is_selected' => $dayData['date'] === $selectedDate,
                    'item_count' => $dayData['item_count'],
                    'preview_items' => $dayData['preview_items'],
                    'hidden_count' => $dayData['hidden_count'],
                ];
            }

            $calendar['weeks'][] = [
                'days' => $days,
                'span_lanes' => [],
            ];
        }

        $calendar['day_lookup'] = array_map(
            fn (array $day): array => [
                'date' => $day['date'],
                'label' => $day['label'],
                'is_current_month' => $day['is_current_month'],
                'item_count' => $day['item_count'],
                'items' => $day['items'],
            ],
            $dayLookup
        );
        $calendar['selected_date'] = $selectedDate;
        $calendar['selected_day'] = $selectedDate !== null
            ? [
                'date' => $dayLookup[$selectedDate]['date'],
                'label' => $dayLookup[$selectedDate]['label'],
                'item_count' => $dayLookup[$selectedDate]['item_count'],
                'items' => $dayLookup[$selectedDate]['items'],
            ]
            : null;

        return $calendar;
    }

    private function resolveMonth(Request $request): CarbonImmutable
    {
        $requestedMonth = trim((string) $request->query('month'));

        if ($requestedMonth !== '') {
            try {
                return CarbonImmutable::createFromFormat('!Y-m', $requestedMonth)->startOfMonth();
            } catch (\Throwable) {
                // Fall back to the current month when the query value is malformed.
            }
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    private function formatMonthLabel(CarbonImmutable $month): string
    {
        $monthLabel = $this->resolveCalendarMonthOptions()[$month->month] ?? null;

        return sprintf('%s %s', $monthLabel ?? self::DEFAULT_MONTH_LABELS[$month->month], $month->year);
    }

    /**
     * @return array<int, string>
     */
    private function resolveCalendarMonthOptions(): array
    {
        $translatedMonths = trans('ui.dashboard.calendar.months');

        if (is_array($translatedMonths) && count($translatedMonths) === 12) {
            return array_combine(range(1, 12), array_values($translatedMonths)) ?: self::DEFAULT_MONTH_LABELS;
        }

        return self::DEFAULT_MONTH_LABELS;
    }

    /**
     * @return list<int>
     */
    private function resolveCalendarYearOptions(?string $monthKey): array
    {
        $month = is_string($monthKey) && $monthKey !== ''
            ? CarbonImmutable::createFromFormat('!Y-m', $monthKey) ?: null
            : null;
        $year = $month?->year ?? CarbonImmutable::now()->year;

        return range($year - 5, $year + 5);
    }

    /**
     * @return list<string>
     */
    private function resolveWeekdayLabels(): array
    {
        $translatedWeekdays = trans('ui.dashboard.calendar.weekdays');

        if (is_array($translatedWeekdays) && count($translatedWeekdays) === 7) {
            return array_values($translatedWeekdays);
        }

        return self::DEFAULT_WEEKDAY_LABELS;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchEventCalendarItems(
        Request $request,
        CarbonImmutable $visibleStart,
        CarbonImmutable $visibleEnd,
        array $texts
    ): Collection {
        $eventsQuery = Event::query()
            ->with([
                'country:id,name_uz,name_ru,iso2',
                'eventType:id,name_uz,name_ru',
            ])
            ->whereIn('status', self::DISPLAY_EVENT_STATUSES)
            ->where(function ($query) use ($visibleStart, $visibleEnd): void {
                $query
                    ->whereBetween('start_datetime', [$visibleStart->startOfDay(), $visibleEnd->endOfDay()])
                    ->orWhere(function ($rangeQuery) use ($visibleStart, $visibleEnd): void {
                        $rangeQuery
                            ->whereNotNull('end_datetime')
                            ->where('start_datetime', '<=', $visibleEnd->endOfDay())
                            ->where('end_datetime', '>=', $visibleStart->startOfDay());
                    });
            });

        $this->applyOwnScope(
            $request,
            $eventsQuery,
            'view events',
            'view own events',
            function ($query, $authUser): void {
                $query->where(function ($eventQuery) use ($authUser): void {
                    $eventQuery
                        ->where('responsible_user_id', $authUser->id)
                        ->orWhere('created_by', $authUser->id);
                });
            }
        );

        return $eventsQuery
            ->orderBy('start_datetime')
            ->orderBy('title_uz')
            ->get()
            ->map(fn (Event $event): array => $this->mapEventCalendarItem($event, $texts));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchVisitCalendarItems(
        Request $request,
        CarbonImmutable $visibleStart,
        CarbonImmutable $visibleEnd,
        array $texts
    ): Collection {
        $visitsQuery = Visit::query()
            ->with([
                'country:id,name_uz,name_ru,iso2',
                'visitType:id,name_uz,name_ru',
            ])
            ->whereIn('status', self::DISPLAY_VISIT_STATUSES)
            ->where(function ($query) use ($visibleStart, $visibleEnd): void {
                $query
                    ->whereBetween('start_date', [$visibleStart->toDateString(), $visibleEnd->toDateString()])
                    ->orWhere(function ($rangeQuery) use ($visibleStart, $visibleEnd): void {
                        $rangeQuery
                            ->whereNotNull('end_date')
                            ->where('start_date', '<=', $visibleEnd->toDateString())
                            ->where('end_date', '>=', $visibleStart->toDateString());
                    });
            });

        $this->applyOwnScope(
            $request,
            $visitsQuery,
            'view visits',
            'view own visits',
            function ($query, $authUser): void {
                $query->where(function ($visitQuery) use ($authUser): void {
                    $visitQuery
                        ->where('responsible_user_id', $authUser->id)
                        ->orWhere('created_by', $authUser->id);
                });
            }
        );

        return $visitsQuery
            ->orderBy('start_date')
            ->orderBy('title_uz')
            ->get()
            ->map(fn (Visit $visit): array => $this->mapVisitCalendarItem($visit, $texts));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function fetchBirthdayCalendarItems(
        CarbonImmutable $calendarStart,
        CarbonImmutable $calendarEnd,
        array $texts
    ): Collection {
        $visibleMonths = [];
        $visibleYears = [];

        for ($cursor = $calendarStart; $cursor->lte($calendarEnd); $cursor = $cursor->addDay()) {
            $visibleMonths[$cursor->month] = true;
            $visibleYears[$cursor->year] = true;
        }

        return PartnerContact::query()
            ->with([
                'partnerOrganization:id,name_uz,name_ru,short_name',
            ])
            ->whereNotNull('birthday')
            ->where(function ($query) use ($visibleMonths): void {
                $monthKeys = array_values(array_keys($visibleMonths));

                foreach ($monthKeys as $index => $monthNumber) {
                    if ($index === 0) {
                        $query->whereMonth('birthday', $monthNumber);

                        continue;
                    }

                    $query->orWhereMonth('birthday', $monthNumber);
                }
            })
            ->orderBy('full_name_uz')
            ->get()
            ->flatMap(function (PartnerContact $partnerContact) use ($calendarStart, $calendarEnd, $visibleYears, $texts): array {
                $birthday = CarbonImmutable::instance($partnerContact->birthday);
                $items = [];

                foreach (array_keys($visibleYears) as $year) {
                    $occurrence = $this->resolveBirthdayOccurrence($birthday, (int) $year);

                    if (! $occurrence->betweenIncluded($calendarStart, $calendarEnd)) {
                        continue;
                    }

                    $items[] = $this->mapBirthdayCalendarItem($partnerContact, $birthday, $occurrence, $texts);
                }

                return $items;
            })
            ->sort(fn (array $left, array $right): int => $this->compareCalendarItems($left, $right))
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEventCalendarItem(Event $event, array $texts): array
    {
        $start = CarbonImmutable::instance($event->start_datetime);
        $end = CarbonImmutable::instance($event->end_datetime ?? $event->start_datetime);
        $state = $this->resolveEventState($event->status);

        return [
            'id' => 'event-'.$event->getKey(),
            'source_id' => $event->getKey(),
            'type' => self::CALENDAR_ITEM_TYPE_EVENT,
            'type_label' => $texts['filter_events'],
            'status' => $state,
            'status_label' => $texts['filter_'.$state],
            'duration_label' => $start->isSameDay($end) ? $texts['duration_single'] : $texts['duration_multi'],
            'icon' => 'event',
            'tone' => sprintf('event-%s', $state),
            'title' => $event->display_title,
            'url' => route('events.show', $event),
            'tooltip' => $this->formatTooltip([
                $event->display_title,
                $texts['filter_events'],
                $texts['filter_'.$state],
                $this->formatDateTimeRange($start, $end),
                $event->eventType?->display_name,
                $event->country?->display_name,
            ]),
            'meta' => implode(' | ', array_filter([
                $event->eventType?->display_name,
                $event->country?->display_name,
            ])),
            'schedule' => $this->formatDateTimeRange($start, $end),
            'schedule_icon' => 'schedule',
            'sort_priority' => $this->resolveStatePriority($state),
            'kind_priority' => 0,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_recurring' => false,
            'recurrence_type' => null,
            'start_cursor' => $start->startOfDay(),
            'end_cursor' => $end->startOfDay(),
            'start_sort' => $start->getTimestamp(),
            'is_multi_day' => ! $start->isSameDay($end),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapVisitCalendarItem(Visit $visit, array $texts): array
    {
        $start = CarbonImmutable::instance($visit->start_date)->startOfDay();
        $end = CarbonImmutable::instance($visit->end_date ?? $visit->start_date)->startOfDay();
        $state = $this->resolveVisitState($visit->status);

        return [
            'id' => 'visit-'.$visit->getKey(),
            'source_id' => $visit->getKey(),
            'type' => self::CALENDAR_ITEM_TYPE_VISIT,
            'type_label' => $texts['filter_visits'],
            'status' => $state,
            'status_label' => $texts['filter_'.$state],
            'duration_label' => $start->isSameDay($end) ? $texts['duration_single'] : $texts['duration_multi'],
            'icon' => 'flight_takeoff',
            'tone' => sprintf('visit-%s', $state),
            'title' => $visit->display_title,
            'url' => route('visits.show', $visit),
            'tooltip' => $this->formatTooltip([
                $visit->display_title,
                $texts['filter_visits'],
                $texts['filter_'.$state],
                $this->formatDateRange($start, $end),
                $visit->visitType?->display_name,
                $visit->country?->display_name,
            ]),
            'meta' => implode(' | ', array_filter([
                $visit->visitType?->display_name,
                $visit->country?->display_name,
                $visit->direction ? (Visit::DIRECTION_LABELS[$visit->direction] ?? null) : null,
            ])),
            'schedule' => $this->formatDateRange($start, $end),
            'schedule_icon' => 'event_available',
            'sort_priority' => $this->resolveStatePriority($state),
            'kind_priority' => 1,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_recurring' => false,
            'recurrence_type' => null,
            'start_cursor' => $start,
            'end_cursor' => $end,
            'start_sort' => $start->getTimestamp(),
            'is_multi_day' => ! $start->isSameDay($end),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapBirthdayCalendarItem(
        PartnerContact $partnerContact,
        CarbonImmutable $birthday,
        CarbonImmutable $occurrence,
        array $texts
    ): array {
        $age = max($occurrence->year - $birthday->year, 0);

        return [
            'id' => 'birthday-'.$partnerContact->getKey().'-'.$occurrence->format('Ymd'),
            'source_id' => $partnerContact->getKey(),
            'type' => self::CALENDAR_ITEM_TYPE_BIRTHDAY,
            'type_label' => $texts['birthday_label'],
            'status' => null,
            'status_label' => null,
            'duration_label' => $texts['birthday_recurring'],
            'icon' => 'cake',
            'tone' => 'birthday',
            'title' => $partnerContact->display_name,
            'url' => route('partner-contacts.show', $partnerContact),
            'tooltip' => $this->formatTooltip([
                $partnerContact->display_name,
                $texts['birthday_label'],
                $this->formatBirthdaySchedule($occurrence, $birthday, $age, $texts),
                $partnerContact->partnerOrganization?->display_name,
            ]),
            'meta' => implode(' | ', array_filter([
                $partnerContact->partnerOrganization?->display_name,
                $partnerContact->display_position,
            ])),
            'schedule' => $this->formatBirthdaySchedule($occurrence, $birthday, $age, $texts),
            'schedule_icon' => 'cake',
            'sort_priority' => -1,
            'kind_priority' => 2,
            'start_date' => $occurrence->toDateString(),
            'end_date' => $occurrence->toDateString(),
            'is_recurring' => true,
            'recurrence_type' => self::CALENDAR_RECURRENCE_YEARLY,
            'start_cursor' => $occurrence,
            'end_cursor' => $occurrence,
            'start_sort' => $occurrence->getTimestamp(),
            'is_multi_day' => false,
        ];
    }

    private function resolveEventState(?string $status): string
    {
        return match ($status) {
            'hozirda' => self::CALENDAR_STATUS_ONGOING,
            'tugatilgan' => self::CALENDAR_STATUS_COMPLETED,
            default => self::CALENDAR_STATUS_PLANNED,
        };
    }

    private function resolveVisitState(?string $status): string
    {
        return match ($status) {
            'ongoing' => self::CALENDAR_STATUS_ONGOING,
            'completed' => self::CALENDAR_STATUS_COMPLETED,
            default => self::CALENDAR_STATUS_PLANNED,
        };
    }

    private function resolveStatePriority(string $state): int
    {
        return match ($state) {
            self::CALENDAR_STATUS_ONGOING => 0,
            self::CALENDAR_STATUS_PLANNED => 1,
            default => 2,
        };
    }

    private function resolveBirthdayOccurrence(CarbonImmutable $birthday, int $year): CarbonImmutable
    {
        $monthStart = CarbonImmutable::create($year, $birthday->month, 1)->startOfDay();
        $day = min($birthday->day, $monthStart->endOfMonth()->day);

        return CarbonImmutable::create($year, $birthday->month, $day)->startOfDay();
    }

    private function compareCalendarItems(array $left, array $right): int
    {
        return [
            $left['sort_priority'],
            $left['kind_priority'],
            $left['start_sort'],
            $left['is_multi_day'] ? 0 : 1,
            mb_strtolower((string) $left['title']),
        ] <=> [
            $right['sort_priority'],
            $right['kind_priority'],
            $right['start_sort'],
            $right['is_multi_day'] ? 0 : 1,
            mb_strtolower((string) $right['title']),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, array<string, mixed>>
     */
    private function buildCalendarDayLookup(
        CarbonImmutable $calendarStart,
        CarbonImmutable $calendarEnd,
        CarbonImmutable $monthStart,
        Collection $items,
        array $texts
    ): array {
        $groupedItems = [];
        $markersByStartDate = [];

        foreach ($items as $item) {
            $cursor = $this->calendarDateCursor($item['start_date'] ?? null)?->max($calendarStart);
            $endCursor = $this->calendarDateCursor($item['end_date'] ?? $item['start_date'] ?? null)?->min($calendarEnd);

            if (! $cursor || ! $endCursor) {
                continue;
            }

            while ($cursor->lte($endCursor)) {
                $groupedItems[$cursor->toDateString()][] = $this->exportCalendarItem($item);
                $cursor = $cursor->addDay();
            }

            $startKey = $item['start_date'] ?? null;
            if (is_string($startKey) && $startKey !== '') {
                $startCursor = $this->calendarDateCursor($startKey);
                if ($startCursor && $startCursor->betweenIncluded($calendarStart, $calendarEnd)) {
                    $exported = $this->exportCalendarItem($item);
                    $markersByStartDate[$startKey][] = [
                        'type' => $exported['type'],
                        'title' => $exported['title'],
                        'url' => $exported['url'],
                        'tooltip' => $exported['tooltip'],
                    ];
                }
            }
        }

        $lookup = [];
        $today = CarbonImmutable::now();

        for ($day = $calendarStart; $day->lte($calendarEnd); $day = $day->addDay()) {
            $dateKey = $day->toDateString();
            $dayItems = $groupedItems[$dateKey] ?? [];
            $startMarkers = $markersByStartDate[$dateKey] ?? [];
            $previewItems = array_slice($startMarkers, 0, self::CALENDAR_DAY_MARKER_LIMIT);

            $lookup[$dateKey] = [
                'date' => $dateKey,
                'label' => $this->formatDayLabel($day),
                'is_current_month' => $day->month === $monthStart->month,
                'is_today' => $day->isSameDay($today),
                'item_count' => count($dayItems),
                'preview_items' => $previewItems,
                'hidden_count' => max(count($startMarkers) - count($previewItems), 0),
                'items' => $dayItems,
                'count_label' => count($dayItems) > 0
                    ? $this->replaceCountPlaceholder($texts['item_count'], count($dayItems))
                    : $texts['empty_count'],
            ];
        }

        return $lookup;
    }

    private function calendarDateCursor(?string $date): ?CarbonImmutable
    {
        if (! is_string($date) || $date === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $date, 'UTC')->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function exportCalendarItem(array $item): array
    {
        return [
            'id' => $item['id'],
            'source_id' => $item['source_id'],
            'type' => $item['type'],
            'type_label' => $item['type_label'],
            'status' => $item['status'],
            'status_label' => $item['status_label'],
            'duration_label' => $item['duration_label'],
            'icon' => $item['icon'],
            'tone' => $item['tone'],
            'title' => $item['title'],
            'url' => $item['url'],
            'tooltip' => $item['tooltip'],
            'meta' => $item['meta'],
            'schedule' => $item['schedule'],
            'schedule_icon' => $item['schedule_icon'],
            'start_date' => $item['start_date'],
            'end_date' => $item['end_date'],
            'is_recurring' => $item['is_recurring'],
            'recurrence_type' => $item['recurrence_type'],
            'is_multi_day' => $item['is_multi_day'],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<int, array{key: string, label: string, count: int, icon: string, tone: string}>
     */
    private function buildCalendarSummary(Collection $items, array $texts): array
    {
        return [
            [
                'key' => 'events',
                'label' => $texts['stat_events'],
                'count' => $items->where('type', self::CALENDAR_ITEM_TYPE_EVENT)->count(),
                'icon' => 'event',
                'tone' => 'event',
            ],
            [
                'key' => 'ongoing',
                'label' => $texts['stat_ongoing'],
                'count' => $items->where('status', self::CALENDAR_STATUS_ONGOING)->count(),
                'icon' => 'autorenew',
                'tone' => 'ongoing',
            ],
            [
                'key' => 'planned',
                'label' => $texts['stat_planned'],
                'count' => $items->where('status', self::CALENDAR_STATUS_PLANNED)->count(),
                'icon' => 'schedule',
                'tone' => 'planned',
            ],
            [
                'key' => 'visits',
                'label' => $texts['stat_visits'],
                'count' => $items->where('type', self::CALENDAR_ITEM_TYPE_VISIT)->count(),
                'icon' => 'flight_takeoff',
                'tone' => 'visit',
            ],
            [
                'key' => 'birthdays',
                'label' => $texts['stat_birthdays'],
                'count' => $items->where('type', self::CALENDAR_ITEM_TYPE_BIRTHDAY)->count(),
                'icon' => 'cake',
                'tone' => 'birthday',
            ],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array{
     *     types: array<int, array{key: string, group: string, value: string, label: string, count: int, icon: string}>,
     *     statuses: array<int, array{key: string, group: string, value: string, label: string, count: int, icon: string}>
     * }
     */
    private function buildCalendarFilters(Collection $items, array $texts): array
    {
        return [
            'types' => [
                [
                    'key' => 'event',
                    'group' => self::CALENDAR_FILTER_GROUP_TYPE,
                    'value' => self::CALENDAR_ITEM_TYPE_EVENT,
                    'label' => $texts['filter_events'],
                    'count' => $items->where('type', self::CALENDAR_ITEM_TYPE_EVENT)->count(),
                    'icon' => 'event',
                ],
                [
                    'key' => 'visit',
                    'group' => self::CALENDAR_FILTER_GROUP_TYPE,
                    'value' => self::CALENDAR_ITEM_TYPE_VISIT,
                    'label' => $texts['filter_visits'],
                    'count' => $items->where('type', self::CALENDAR_ITEM_TYPE_VISIT)->count(),
                    'icon' => 'flight_takeoff',
                ],
                [
                    'key' => 'birthday',
                    'group' => self::CALENDAR_FILTER_GROUP_TYPE,
                    'value' => self::CALENDAR_ITEM_TYPE_BIRTHDAY,
                    'label' => $texts['filter_birthdays'],
                    'count' => $items->where('type', self::CALENDAR_ITEM_TYPE_BIRTHDAY)->count(),
                    'icon' => 'cake',
                ],
            ],
            'statuses' => [
                [
                    'key' => 'all',
                    'group' => self::CALENDAR_FILTER_GROUP_STATUS,
                    'value' => 'all',
                    'label' => $texts['filter_all'],
                    'count' => $items->count(),
                    'icon' => 'dashboard',
                ],
                [
                    'key' => 'planned',
                    'group' => self::CALENDAR_FILTER_GROUP_STATUS,
                    'value' => self::CALENDAR_STATUS_PLANNED,
                    'label' => $texts['filter_planned'],
                    'count' => $items->where('status', self::CALENDAR_STATUS_PLANNED)->count(),
                    'icon' => 'schedule',
                ],
                [
                    'key' => 'ongoing',
                    'group' => self::CALENDAR_FILTER_GROUP_STATUS,
                    'value' => self::CALENDAR_STATUS_ONGOING,
                    'label' => $texts['filter_ongoing'],
                    'count' => $items->where('status', self::CALENDAR_STATUS_ONGOING)->count(),
                    'icon' => 'autorenew',
                ],
                [
                    'key' => 'completed',
                    'group' => self::CALENDAR_FILTER_GROUP_STATUS,
                    'value' => self::CALENDAR_STATUS_COMPLETED,
                    'label' => $texts['filter_completed'],
                    'count' => $items->where('status', self::CALENDAR_STATUS_COMPLETED)->count(),
                    'icon' => 'check_circle',
                ],
            ],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $dayLookup
     */
    private function resolveSelectedCalendarDate(
        array $dayLookup,
        CarbonImmutable $monthStart,
        CarbonImmutable $monthEnd
    ): ?string {
        $today = CarbonImmutable::now();

        if ($today->betweenIncluded($monthStart, $monthEnd)) {
            $todayKey = $today->toDateString();

            if (isset($dayLookup[$todayKey])) {
                return $todayKey;
            }
        }

        foreach ($dayLookup as $day) {
            if ($day['is_current_month'] && $day['item_count'] > 0) {
                return $day['date'];
            }
        }

        return $monthStart->toDateString();
    }

    private function formatDayLabel(CarbonImmutable $day): string
    {
        $weekdayLabel = self::DEFAULT_WEEKDAY_NAMES[$day->dayOfWeekIso] ?? '';
        $monthLabel = $this->formatMonthLabel($day->startOfMonth());

        return trim(sprintf('%d %s, %s', $day->day, strtok($monthLabel, ' '), $weekdayLabel), ', ');
    }

    private function formatDateTimeRange(CarbonImmutable $start, CarbonImmutable $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->format('d.m.Y H:i').' - '.$end->format('H:i');
        }

        return $start->format('d.m.Y H:i').' - '.$end->format('d.m.Y H:i');
    }

    private function formatDateRange(CarbonImmutable $start, CarbonImmutable $end): string
    {
        if ($start->isSameDay($end)) {
            return $start->format('d.m.Y');
        }

        return $start->format('d.m.Y').' - '.$end->format('d.m.Y');
    }

    /**
     * @param  array<int, string|null>  $parts
     */
    private function formatTooltip(array $parts): string
    {
        return implode(' | ', array_values(array_filter($parts)));
    }

    /**
     * @return array<string, string>
     */
    private function resolveCalendarTexts(): array
    {
        return [
            'subtitle' => $this->translateOrFallback(
                'ui.dashboard.calendar.subtitle_compact',
                "Bir kunlik va ko'p kunlik tadbirlar, tashriflar hamda hamkorlarning tug'ilgan kunlari kun va hafta kesimida aniq ko'rinishda beriladi."
            ),
            'all_events' => $this->translateOrFallback('ui.dashboard.calendar.all_events', 'Barcha tadbirlar'),
            'all_visits' => $this->translateOrFallback('ui.dashboard.calendar.all_visits', 'Barcha tashriflar'),
            'all_contacts' => $this->translateOrFallback('ui.dashboard.calendar.all_contacts', 'Hamkor kontaktlar'),
            'item_count' => $this->translateOrFallback('ui.dashboard.calendar.item_count', ':count ta yozuv'),
            'empty_count' => $this->translateOrFallback('ui.dashboard.calendar.empty_count', "Bo'sh kun"),
            'empty_state' => $this->translateOrFallback(
                'ui.dashboard.calendar.empty_active',
                "Tanlangan oy bo'yicha ko'rsatish uchun tadbir yoki tashrif topilmadi."
            ),
            'empty_filtered' => $this->translateOrFallback(
                'ui.dashboard.calendar.empty_filtered',
                "Tanlangan filtr bo'yicha bu sana uchun yozuv topilmadi."
            ),
            'detail_eyebrow' => $this->translateOrFallback('ui.dashboard.calendar.detail_eyebrow', 'Kun tafsiloti'),
            'detail_empty' => $this->translateOrFallback(
                'ui.dashboard.calendar.detail_empty',
                "Bu sana uchun ko'rsatish mumkin bo'lgan yozuv yo'q."
            ),
            'more_items' => $this->translateOrFallback('ui.dashboard.calendar.more_items', '+:count'),
            'duration_single' => $this->translateOrFallback('ui.dashboard.calendar.duration.single', 'Bir kunlik'),
            'duration_multi' => $this->translateOrFallback('ui.dashboard.calendar.duration.multi', "Ko'p kunlik"),
            'birthday_label' => $this->translateOrFallback('ui.dashboard.calendar.birthday.label', "Tug'ilgan kun"),
            'birthday_recurring' => $this->translateOrFallback('ui.dashboard.calendar.birthday.recurring', 'Har yili'),
            'birthday_age_suffix' => $this->translateOrFallback('ui.dashboard.calendar.birthday.age_suffix', 'yosh'),
            'filter_all' => $this->translateOrFallback('ui.dashboard.calendar.filters.all', 'Barchasi'),
            'filter_events' => $this->translateOrFallback('ui.dashboard.calendar.filters.events', 'Tadbirlar'),
            'filter_visits' => $this->translateOrFallback('ui.dashboard.calendar.filters.visits', 'Tashriflar'),
            'filter_birthdays' => $this->translateOrFallback('ui.dashboard.calendar.filters.birthdays', "Tug'ilgan kunlar"),
            'filter_planned' => $this->translateOrFallback('ui.dashboard.calendar.filters.planned', 'Rejalashtirilgan'),
            'filter_ongoing' => $this->translateOrFallback('ui.dashboard.calendar.filters.ongoing', 'Jarayonda'),
            'filter_completed' => $this->translateOrFallback('ui.dashboard.calendar.filters.completed', 'Tugatilgan'),
            'stat_events' => $this->translateOrFallback('ui.dashboard.calendar.stats.events', 'Tadbirlar'),
            'stat_ongoing' => $this->translateOrFallback('ui.dashboard.calendar.stats.ongoing', 'Jarayonda'),
            'stat_planned' => $this->translateOrFallback('ui.dashboard.calendar.stats.planned', 'Rejalashtirilgan'),
            'stat_visits' => $this->translateOrFallback('ui.dashboard.calendar.stats.visits', 'Tashriflar'),
            'stat_birthdays' => $this->translateOrFallback('ui.dashboard.calendar.stats.birthdays', "Tug'ilgan kunlar"),
        ];
    }

    private function translateOrFallback(string $key, string $fallback): string
    {
        $translated = trans($key);

        return is_string($translated) && $translated !== $key
            ? $translated
            : $fallback;
    }

    private function replaceCountPlaceholder(string $template, int $count): string
    {
        return str_replace(':count', (string) $count, $template);
    }

    private function formatBirthdaySchedule(
        CarbonImmutable $occurrence,
        CarbonImmutable $birthday,
        int $age,
        array $texts
    ): string {
        $ageText = $birthday->year > 0 && $age > 0
            ? $age.' '.$texts['birthday_age_suffix']
            : null;

        return implode(' | ', array_filter([
            $occurrence->format('d.m.Y'),
            $texts['birthday_recurring'],
            $ageText,
        ]));
    }
}

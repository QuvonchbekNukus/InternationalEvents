<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const DEFAULT_WEEKDAY_LABELS = ['Du', 'Se', 'Cho', 'Pay', 'Ju', 'Sha', 'Yak'];

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

    private const EVENT_COLOR_KEYS = [
        'sky',
        'emerald',
        'amber',
        'violet',
        'rose',
        'cyan',
        'lime',
    ];

    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'eventCalendar' => $this->buildEventCalendar($request),
        ]);
    }

    /**
     * @return array{
     *     has_access: bool,
     *     month_key: string,
     *     month_label: string,
     *     day_labels: list<string>,
     *     prev_url: string,
     *     next_url: string,
     *     events_url: ?string,
     *     event_count: int,
     *     weeks: array<int, array{days: array<int, array{date: string, day_number: int, is_current_month: bool, is_today: bool}>, lanes: array<int, array<int, array{url: string, title: string, color: string, tooltip: string, start_column: int, span: int, starts_before: bool, ends_after: bool}>>}>,
     * }
     */
    private function buildEventCalendar(Request $request): array
    {
        $month = $this->resolveMonth($request);
        $monthStart = $month->startOfMonth();
        $monthEnd = $month->endOfMonth();

        $calendar = [
            'has_access' => false,
            'month_key' => $monthStart->format('Y-m'),
            'month_label' => $this->formatMonthLabel($monthStart),
            'day_labels' => $this->resolveWeekdayLabels(),
            'prev_url' => route('dashboard', ['month' => $monthStart->subMonth()->format('Y-m')]),
            'next_url' => route('dashboard', ['month' => $monthStart->addMonth()->format('Y-m')]),
            'events_url' => null,
            'event_count' => 0,
            'weeks' => [],
        ];

        $user = $request->user();

        if (! $user?->can('view events') && ! $user?->can('view own events')) {
            return $calendar;
        }

        $calendar['has_access'] = true;
        $calendar['events_url'] = route('events.index');

        $eventsQuery = Event::query()
            ->with([
                'country:id,name_uz,name_ru,name_cryl,iso2',
                'eventType:id,name_uz,name_ru,name_cryl',
            ])
            ->where(function ($query) use ($monthStart, $monthEnd): void {
                $query
                    ->whereBetween('start_datetime', [$monthStart->startOfDay(), $monthEnd->endOfDay()])
                    ->orWhere(function ($rangeQuery) use ($monthStart, $monthEnd): void {
                        $rangeQuery
                            ->whereNotNull('end_datetime')
                            ->where('start_datetime', '<=', $monthEnd->endOfDay())
                            ->where('end_datetime', '>=', $monthStart->startOfDay());
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

        $events = $eventsQuery
            ->orderBy('start_datetime')
            ->orderBy('title_uz')
            ->get();

        $calendar['event_count'] = $events->count();

        $colorMap = [];
        foreach ($events as $event) {
            $colorMap[$event->getKey()] = self::EVENT_COLOR_KEYS[abs(crc32((string) $event->getKey())) % count(self::EVENT_COLOR_KEYS)];
        }

        $calendarStart = $monthStart->startOfWeek(CarbonImmutable::MONDAY);
        $calendarEnd = $monthEnd->endOfWeek(CarbonImmutable::SUNDAY);

        for ($weekStart = $calendarStart; $weekStart->lte($calendarEnd); $weekStart = $weekStart->addWeek()) {
            $weekEnd = $weekStart->endOfWeek(CarbonImmutable::SUNDAY);

            $calendar['weeks'][] = [
                'days' => $this->buildWeekDays($weekStart, $monthStart),
                'lanes' => $this->buildWeekLanes($events, $weekStart, $weekEnd, $colorMap),
            ];
        }

        return $calendar;
    }

    private function resolveMonth(Request $request): CarbonImmutable
    {
        $requestedMonth = trim((string) $request->query('month'));

        if ($requestedMonth !== '') {
            try {
                return CarbonImmutable::createFromFormat('Y-m', $requestedMonth)->startOfMonth();
            } catch (\Throwable) {
                // Fall back to the current month when the query value is malformed.
            }
        }

        return CarbonImmutable::now()->startOfMonth();
    }

    private function formatMonthLabel(CarbonImmutable $month): string
    {
        $translatedMonths = trans('ui.dashboard.calendar.months');
        $monthLabel = is_array($translatedMonths)
            ? ($translatedMonths[$month->month] ?? null)
            : null;

        return sprintf('%s %s', $monthLabel ?? self::DEFAULT_MONTH_LABELS[$month->month], $month->year);
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
     * @return array<int, array{date: string, day_number: int, is_current_month: bool, is_today: bool}>
     */
    private function buildWeekDays(CarbonImmutable $weekStart, CarbonImmutable $monthStart): array
    {
        $days = [];
        $today = CarbonImmutable::now();

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $day = $weekStart->addDays($dayOffset);

            $days[] = [
                'date' => $day->toDateString(),
                'day_number' => $day->day,
                'is_current_month' => $day->month === $monthStart->month,
                'is_today' => $day->isSameDay($today),
            ];
        }

        return $days;
    }

    /**
     * @param Collection<int, Event> $events
     * @param array<int, string> $colorMap
     * @return array<int, array<int, array{url: string, title: string, color: string, tooltip: string, start_column: int, span: int, starts_before: bool, ends_after: bool}>>
     */
    private function buildWeekLanes(
        Collection $events,
        CarbonImmutable $weekStart,
        CarbonImmutable $weekEnd,
        array $colorMap
    ): array {
        $laneEndColumns = [];
        $lanes = [];

        foreach ($events as $event) {
            $eventStart = CarbonImmutable::instance($event->start_datetime)->startOfDay();
            $eventEnd = CarbonImmutable::instance($event->end_datetime ?? $event->start_datetime)->startOfDay();

            if ($eventEnd->lt($weekStart) || $eventStart->gt($weekEnd)) {
                continue;
            }

            $segmentStart = $eventStart->max($weekStart);
            $segmentEnd = $eventEnd->min($weekEnd);
            $startColumn = $weekStart->diffInDays($segmentStart) + 1;
            $span = $segmentStart->diffInDays($segmentEnd) + 1;

            $segment = [
                'url' => route('events.show', $event),
                'title' => $event->display_title,
                'color' => $colorMap[$event->getKey()] ?? self::EVENT_COLOR_KEYS[0],
                'tooltip' => $this->buildEventTooltip($event),
                'start_column' => $startColumn,
                'span' => $span,
                'starts_before' => $eventStart->lt($weekStart),
                'ends_after' => $eventEnd->gt($weekEnd),
            ];

            $laneIndex = $this->resolveLaneIndex($laneEndColumns, $startColumn);
            $laneEndColumns[$laneIndex] = $startColumn + $span - 1;
            $lanes[$laneIndex][] = $segment;
        }

        if ($lanes === []) {
            return [[]];
        }

        ksort($lanes);

        return array_values($lanes);
    }

    /**
     * @param array<int, int> $laneEndColumns
     */
    private function resolveLaneIndex(array $laneEndColumns, int $startColumn): int
    {
        foreach ($laneEndColumns as $laneIndex => $lastColumn) {
            if ($startColumn > $lastColumn) {
                return $laneIndex;
            }
        }

        return count($laneEndColumns);
    }

    private function buildEventTooltip(Event $event): string
    {
        $start = CarbonImmutable::instance($event->start_datetime);
        $end = CarbonImmutable::instance($event->end_datetime ?? $event->start_datetime);
        $range = $start->format('d.m.Y H:i');

        if (! $start->equalTo($end)) {
            $range .= ' - '.$end->format('d.m.Y H:i');
        }

        $meta = array_filter([
            $event->eventType?->display_name,
            $event->country?->display_name,
        ]);

        return trim($event->display_title.' | '.implode(' | ', $meta).' | '.$range, ' |');
    }
}

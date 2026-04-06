@extends('layouts.dashboard')

@section('title', __('ui.dashboard.page_title'))

@section('content')
    @php
        $currentRole = auth()->user()?->getRoleNames()->first();
        $translatedRole = $currentRole ? __("ui.roles.$currentRole") : __('ui.roles.unassigned');
        $roleLabel = $currentRole && $translatedRole === "ui.roles.$currentRole"
            ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $currentRole))
            : $translatedRole;
        $dashboardGeoJsonMap = [
            'eyebrow' => __('ui.map.geojson.eyebrow'),
            'title' => __('ui.map.geojson.title'),
            'subtitle' => '',
            'height' => 460,
            'center' => [20, 0],
            'zoom' => 2,
            'chips' => [],
        ];
        $resourceCards = [
            [
                'permission' => 'view users',
                'title' => __('ui.dashboard.cards.users.title'),
                'count' => \App\Models\User::count(),
                'description' => __('ui.dashboard.cards.users.description'),
                'icon' => 'group',
                'tone' => 'azure',
                'route' => route('users.index'),
                'action' => __('ui.dashboard.cards.users.action'),
            ],
            [
                'permission' => 'view departments',
                'title' => __('ui.dashboard.cards.departments.title'),
                'count' => \App\Models\Department::count(),
                'description' => __('ui.dashboard.cards.departments.description'),
                'icon' => 'business_center',
                'tone' => 'emerald',
                'route' => route('departments.index'),
                'action' => __('ui.dashboard.cards.departments.action'),
            ],
            [
                'permission' => 'view ranks',
                'title' => __('ui.dashboard.cards.ranks.title'),
                'count' => \App\Models\Rank::count(),
                'description' => __('ui.dashboard.cards.ranks.description'),
                'icon' => 'stars',
                'tone' => 'amber',
                'route' => route('ranks.index'),
                'action' => __('ui.dashboard.cards.ranks.action'),
            ],
            [
                'permission' => 'view countries',
                'title' => __('ui.dashboard.cards.countries.title'),
                'count' => \App\Models\Country::count(),
                'description' => __('ui.dashboard.cards.countries.description'),
                'icon' => 'public',
                'tone' => 'cyan',
                'route' => route('countries.index'),
                'action' => __('ui.dashboard.cards.countries.action'),
            ],
            [
                'permission' => 'view agreements',
                'title' => __('ui.dashboard.cards.agreements.title'),
                'count' => \App\Models\Agreement::count(),
                'description' => __('ui.dashboard.cards.agreements.description'),
                'icon' => 'description',
                'tone' => 'indigo',
                'route' => route('agreements.index'),
                'action' => __('ui.dashboard.cards.agreements.action'),
            ],
            [
                'permission' => 'view agreement types',
                'title' => __('ui.dashboard.cards.agreement_types.title'),
                'count' => \App\Models\AgreementType::count(),
                'description' => __('ui.dashboard.cards.agreement_types.description'),
                'icon' => 'toc',
                'tone' => 'violet',
                'route' => route('agreement-types.index'),
                'action' => __('ui.dashboard.cards.agreement_types.action'),
            ],
            [
                'permission' => 'view agreement directions',
                'title' => __('ui.dashboard.cards.agreement_directions.title'),
                'count' => \App\Models\AgreementDirection::count(),
                'description' => __('ui.dashboard.cards.agreement_directions.description'),
                'icon' => 'timeline',
                'tone' => 'rose',
                'route' => route('agreement-directions.index'),
                'action' => __('ui.dashboard.cards.agreement_directions.action'),
            ],
            [
                'permission' => 'view organization types',
                'title' => __('ui.dashboard.cards.organization_types.title'),
                'count' => \App\Models\OrganizationType::count(),
                'description' => __('ui.dashboard.cards.organization_types.description'),
                'icon' => 'domain',
                'tone' => 'teal',
                'route' => route('organization-types.index'),
                'action' => __('ui.dashboard.cards.organization_types.action'),
            ],
            [
                'permission' => 'view partner organizations',
                'title' => __('ui.dashboard.cards.partner_organizations.title'),
                'count' => \App\Models\PartnerOrganization::count(),
                'description' => __('ui.dashboard.cards.partner_organizations.description'),
                'icon' => 'business',
                'tone' => 'sky',
                'route' => route('partner-organizations.index'),
                'action' => __('ui.dashboard.cards.partner_organizations.action'),
            ],
            [
                'permission' => 'view partner contacts',
                'title' => __('ui.dashboard.cards.partner_contacts.title'),
                'count' => \App\Models\PartnerContact::count(),
                'description' => __('ui.dashboard.cards.partner_contacts.description'),
                'icon' => 'contacts',
                'tone' => 'orange',
                'route' => route('partner-contacts.index'),
                'action' => __('ui.dashboard.cards.partner_contacts.action'),
            ],
        ];
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.dashboard.eyebrow') }}</p>
                <h1 class="page-title">{{ __('ui.dashboard.title') }}</h1>
            </div>

            <div class="context-chip">
                <i class="material-icons app-icon app-icon--md" aria-hidden="true">verified_user</i>
                <span>{{ $roleLabel }}</span>
            </div>
        </div>

        @if (($eventCalendar['has_access'] ?? false) === true)
            @php
                $calendarTexts = $eventCalendar['texts'] ?? [];
                $selectedDay = $eventCalendar['selected_day'] ?? null;
                $calendarMonth = \Carbon\CarbonImmutable::createFromFormat('!Y-m', $eventCalendar['month_key']);
            @endphp
            <section class="content-card dashboard-calendar-card" data-calendar-card data-calendar-endpoint="{{ $calendarDataUrl }}">
                <div class="section-heading dashboard-calendar-card__head">
                    <div class="dashboard-calendar-card__intro">
                        <p class="eyebrow">{{ __('ui.dashboard.calendar.eyebrow') }}</p>
                        <div class="dashboard-calendar-card__title-row">
                            <h2 class="section-title">{{ __('ui.dashboard.calendar.title') }}</h2>
                            <span class="badge" data-calendar-total-count>{{ $eventCalendar['count_label'] }}</span>
                        </div>
                    </div>

                    <div class="dashboard-calendar-card__controls">
                        <div class="dashboard-calendar-card__month-nav">
                            <a
                                class="btn btn--ghost dashboard-calendar-card__nav"
                                href="{{ $eventCalendar['prev_url'] }}"
                                aria-label="{{ __('ui.dashboard.calendar.previous_month') }}"
                                data-calendar-nav="prev"
                            >
                                <i class="material-icons" aria-hidden="true">chevron_left</i>
                            </a>

                            <div class="dashboard-calendar-card__month">
                                <form class="dashboard-calendar-card__period-form" method="GET" action="{{ route('dashboard') }}" data-calendar-period-form aria-label="{{ $eventCalendar['month_label'] }}">
                                    <input type="hidden" name="month" value="{{ $eventCalendar['month_key'] }}" data-calendar-period-value>

                                    <label class="dashboard-calendar-card__select-wrap">
                                        <select
                                            class="dashboard-calendar-card__select dashboard-calendar-card__select--month"
                                            aria-label="{{ __('ui.common.calendar_aria.pick_month') }}"
                                            data-calendar-period-month
                                        >
                                            @foreach ($calendarMonthOptions as $monthNumber => $monthLabel)
                                                <option value="{{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }}" @selected($calendarMonth->month === (int) $monthNumber)>
                                                    {{ $monthLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="material-icons" aria-hidden="true">expand_more</i>
                                    </label>

                                    <label class="dashboard-calendar-card__select-wrap dashboard-calendar-card__select-wrap--year">
                                        <select
                                            class="dashboard-calendar-card__select dashboard-calendar-card__select--year"
                                            aria-label="{{ __('ui.common.calendar_aria.pick_year') }}"
                                            data-calendar-period-year
                                        >
                                            @foreach ($calendarYearOptions as $yearOption)
                                                <option value="{{ $yearOption }}" @selected($calendarMonth->year === $yearOption)>
                                                    {{ $yearOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="material-icons" aria-hidden="true">expand_more</i>
                                    </label>
                                </form>
                            </div>

                            <a
                                class="btn btn--ghost dashboard-calendar-card__nav"
                                href="{{ $eventCalendar['next_url'] }}"
                                aria-label="{{ __('ui.dashboard.calendar.next_month') }}"
                                data-calendar-nav="next"
                            >
                                <i class="material-icons" aria-hidden="true">chevron_right</i>
                            </a>
                        </div>

                        @if ($eventCalendar['listing_url'])
                            <a class="btn btn--ghost dashboard-calendar-card__link" href="{{ $eventCalendar['listing_url'] }}" data-calendar-listing-link>
                                <i class="material-icons" aria-hidden="true">event_note</i>
                                <span data-calendar-listing-label>{{ $eventCalendar['listing_label'] }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="dashboard-calendar-card__toolbar">
                    <div class="dashboard-calendar-card__filter-layout" aria-label="Tadbirlar moduli filterlari">
                        <div class="dashboard-calendar-card__filter-group">
                            <div class="dashboard-calendar-card__filter-head">
                                <div>
                                    <p class="dashboard-calendar-card__filter-label">Turlar</p>
                                </div>
                            </div>
                            <div class="dashboard-calendar-card__filters dashboard-calendar-card__filters--type" role="group" aria-label="Turlar bo'yicha filterlar">
                                @foreach ($eventCalendar['filters']['types'] ?? [] as $filter)
                                    <button
                                        class="dashboard-calendar-card__filter dashboard-calendar-card__filter--type"
                                        type="button"
                                        data-calendar-filter="{{ $filter['key'] }}"
                                        data-calendar-filter-group="{{ $filter['group'] }}"
                                        data-calendar-filter-value="{{ $filter['value'] }}"
                                        aria-label="{{ $filter['label'] }}"
                                        aria-pressed="false"
                                        title="{{ $filter['label'] }}"
                                    >
                                        <i class="material-icons" aria-hidden="true">{{ $filter['icon'] }}</i>
                                        <span>{{ $filter['label'] }}</span>
                                        <strong data-calendar-filter-count>{{ $filter['count'] }}</strong>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="dashboard-calendar-card__filter-group">
                            <div class="dashboard-calendar-card__filter-head">
                                <div>
                                    <p class="dashboard-calendar-card__filter-label">Status</p>
                                </div>
                            </div>
                            <div class="dashboard-calendar-card__filters dashboard-calendar-card__filters--status" role="radiogroup" aria-label="Status bo'yicha filterlar">
                                @foreach ($eventCalendar['filters']['statuses'] ?? [] as $filter)
                                    <button
                                        class="dashboard-calendar-card__filter dashboard-calendar-card__filter--status {{ $filter['value'] === 'all' ? 'is-active' : '' }}"
                                        type="button"
                                        role="radio"
                                        data-calendar-filter="{{ $filter['key'] }}"
                                        data-calendar-filter-group="{{ $filter['group'] }}"
                                        data-calendar-filter-value="{{ $filter['value'] }}"
                                        aria-label="{{ $filter['label'] }}"
                                        aria-checked="{{ $filter['value'] === 'all' ? 'true' : 'false' }}"
                                        title="{{ $filter['label'] }}"
                                    >
                                        <i class="material-icons" aria-hidden="true">{{ $filter['icon'] }}</i>
                                        <span>{{ $filter['label'] }}</span>
                                        <strong data-calendar-filter-count>{{ $filter['count'] }}</strong>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dashboard-calendar-card__async-feedback" data-calendar-feedback hidden role="status" aria-live="polite"></div>

                <div
                    class="event-calendar-compact"
                    data-calendar-module
                    data-selected-date="{{ $eventCalendar['selected_date'] }}"
                    data-month-label="{{ $eventCalendar['month_label'] }}"
                    data-count-template="{{ $calendarTexts['item_count'] }}"
                    data-empty-count-label="{{ $calendarTexts['empty_count'] }}"
                    data-more-template="{{ $calendarTexts['more_items'] }}"
                    data-detail-empty="{{ $calendarTexts['detail_empty'] }}"
                    data-detail-filter-empty="{{ $calendarTexts['empty_filtered'] }}"
                    aria-label="{{ __('ui.dashboard.calendar.aria') }}"
                >
                        <div class="event-calendar-compact__main">
                            <div class="event-calendar-compact__weekdays">
                                @foreach ($eventCalendar['day_labels'] as $dayLabel)
                                    <div class="event-calendar-compact__weekday">{{ $dayLabel }}</div>
                                @endforeach
                            </div>

                            <div class="event-calendar-compact__weeks">
                                @foreach ($eventCalendar['weeks'] as $week)
                                    @php
                                        $weekLaneCount = count($week['span_lanes'] ?? []);
                                        $weekLaneHeight = $weekLaneCount > 0 ? ($weekLaneCount * 32) + (($weekLaneCount - 1) * 6) : 0;
                                    @endphp
                                    <section class="event-calendar-compact__week" style="--week-lane-height: {{ $weekLaneHeight }}px;">
                                        <div class="event-calendar-compact__days">
                                            @foreach ($week['days'] as $day)
                                                <button
                                                    type="button"
                                                    class="event-calendar-compact__day {{ $day['is_current_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }} {{ $day['is_selected'] ? 'is-selected' : '' }} {{ $day['item_count'] > 0 ? 'has-items' : '' }}"
                                                    data-calendar-day
                                                    data-date="{{ $day['date'] }}"
                                                    data-current-month="{{ $day['is_current_month'] ? 'true' : 'false' }}"
                                                    aria-label="{{ $day['label'] }}, {{ $day['item_count'] > 0 ? str_replace(':count', $day['item_count'], $calendarTexts['item_count']) : $calendarTexts['empty_count'] }}"
                                                    aria-pressed="{{ $day['is_selected'] ? 'true' : 'false' }}"
                                                >
                                                    <span class="event-calendar-compact__day-head">
                                                        <time datetime="{{ $day['date'] }}">{{ $day['day_number'] }}</time>
                                                        <span class="event-calendar-compact__day-count" data-day-count @if ($day['item_count'] === 0) hidden @endif>
                                                            {{ $day['item_count'] }}
                                                        </span>
                                                    </span>

                                                    <span class="event-calendar-compact__items event-calendar-compact__markers" data-day-preview>
                                                        @foreach ($day['preview_items'] as $marker)
                                                            <span
                                                                class="event-calendar-compact__marker event-calendar-compact__marker--{{ $marker['type'] }}"
                                                                role="link"
                                                                tabindex="0"
                                                                data-calendar-marker-url="{{ $marker['url'] }}"
                                                                title="{{ $marker['tooltip'] }}"
                                                                aria-label="{{ $marker['title'] }}"
                                                                data-calendar-item-type="{{ $marker['type'] }}"
                                                            >
                                                                <span class="event-calendar-compact__marker-dot" aria-hidden="true"></span>
                                                            </span>
                                                        @endforeach
                                                    </span>

                                                    <span class="event-calendar-compact__more" data-day-more @if ($day['hidden_count'] === 0) hidden @endif>
                                                        {{ str_replace(':count', $day['hidden_count'], $calendarTexts['more_items']) }}
                                                    </span>
                                                </button>
                                            @endforeach
                                        </div>

                                        @if (($week['span_lanes'] ?? []) !== [])
                                            <div class="event-calendar-compact__lanes">
                                                @foreach ($week['span_lanes'] as $lane)
                                                    <div class="event-calendar-compact__lane" data-calendar-lane>
                                                        @foreach ($lane as $segment)
                                                            <a
                                                                class="event-calendar-compact__span event-calendar-compact__span--{{ $segment['tone'] }} {{ $segment['type'] }}-span {{ $segment['starts_before'] ? 'is-continued-left' : 'is-start' }} {{ $segment['ends_after'] ? 'is-continued-right' : 'is-end' }}"
                                                                href="{{ $segment['url'] }}"
                                                                title="{{ $segment['tooltip'] }}"
                                                                style="grid-column: {{ $segment['start_column'] }} / span {{ $segment['span'] }}"
                                                                data-calendar-span
                                                                data-item-id="{{ $segment['id'] }}"
                                                                data-type="{{ $segment['type'] }}"
                                                                data-status="{{ $segment['status'] ?? '' }}"
                                                            >
                                                                <span class="event-calendar-compact__span-prefix">
                                                                    <i class="material-icons" aria-hidden="true">{{ $segment['icon'] }}</i>
                                                                </span>
                                                                <span class="event-calendar-compact__span-title">{{ $segment['title'] }}</span>
                                                                <span class="event-calendar-compact__span-meta">{{ $segment['status_label'] ?? $segment['type_label'] }}</span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </section>
                                @endforeach
                            </div>
                        </div>

                        <aside class="event-calendar-compact__detail" aria-live="polite">
                            <div class="event-calendar-compact__detail-head">
                                <div>
                                    <p class="eyebrow">{{ $calendarTexts['detail_eyebrow'] }}</p>
                                    <h3 class="event-calendar-compact__detail-title" data-calendar-detail-date>
                                        {{ $selectedDay['label'] ?? $eventCalendar['month_label'] }}
                                    </h3>
                                    <p class="event-calendar-compact__detail-count" data-calendar-detail-count>
                                        {{ isset($selectedDay['item_count']) && $selectedDay['item_count'] > 0 ? str_replace(':count', $selectedDay['item_count'], $calendarTexts['item_count']) : $calendarTexts['empty_count'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="event-calendar-compact__detail-body" data-calendar-detail-body>
                                @foreach (($selectedDay['items'] ?? []) as $item)
                                    <a class="event-calendar-compact__detail-item event-calendar-compact__detail-item--{{ $item['tone'] }}" data-calendar-item-type="{{ $item['type'] }}" data-status="{{ $item['status'] ?? '' }}" href="{{ $item['url'] }}">
                                        <div class="event-calendar-compact__detail-surface">
                                            <div class="event-calendar-compact__detail-tags">
                                                <span class="event-calendar-compact__detail-tag event-calendar-compact__detail-tag--type event-calendar-compact__detail-tag--type-{{ $item['type'] }} {{ $item['type'] }}-chip">
                                                    <i class="material-icons" aria-hidden="true">{{ $item['icon'] }}</i>
                                                    <span>{{ $item['type_label'] }}</span>
                                                </span>
                                                @if (($item['status_label'] ?? null) !== null)
                                                    <span class="event-calendar-compact__detail-tag event-calendar-compact__detail-tag--status event-calendar-compact__detail-tag--status-{{ $item['status'] }}">
                                                        {{ $item['status_label'] }}
                                                    </span>
                                                @endif
                                                <span class="event-calendar-compact__detail-tag event-calendar-compact__detail-tag--duration">
                                                    {{ $item['duration_label'] }}
                                                </span>
                                            </div>
                                            <strong class="event-calendar-compact__detail-item-title">{{ $item['title'] }}</strong>
                                            <div class="event-calendar-compact__detail-meta">
                                                <span>
                                                    <i class="material-icons" aria-hidden="true">{{ $item['schedule_icon'] }}</i>
                                                    <span>{{ $item['schedule'] }}</span>
                                                </span>
                                                @if ($item['meta'] !== '')
                                                    <span>
                                                        <i class="material-icons" aria-hidden="true">public</i>
                                                        <span>{{ $item['meta'] }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <i class="material-icons event-calendar-compact__detail-arrow" aria-hidden="true">open_in_new</i>
                                    </a>
                                @endforeach
                            </div>

                            @if (($selectedDay['items'] ?? []) === [])
                                <div class="event-calendar-compact__detail-empty" data-calendar-detail-empty>
                                    {{ $calendarTexts['detail_empty'] }}
                                </div>
                            @else
                                <div class="event-calendar-compact__detail-empty" data-calendar-detail-empty hidden></div>
                            @endif
                        </aside>

                        <script type="application/json" data-calendar-day-lookup>
                            @json($eventCalendar['day_lookup'])
                        </script>
                        <script type="application/json" data-calendar-items>
                            @json($eventCalendar['items'] ?? [])
                        </script>
                        <script type="application/json" data-calendar-payload>
                            @json($eventCalendar)
                        </script>
                        <script type="application/json" data-calendar-period-options>
                            @json([
                                'months' => $calendarMonthOptions,
                                'years' => $calendarYearOptions,
                            ])
                        </script>
                </div>

                <div class="dashboard-calendar-card__loading" data-calendar-loading hidden>
                    <span class="dashboard-calendar-card__spinner" aria-hidden="true"></span>
                    <span>{{ __('ui.common.loading.calendar_refresh') }}</span>
                </div>
            </section>
        @endif

        <x-dashboard-geojson-map
            :eyebrow="$dashboardGeoJsonMap['eyebrow']"
            :title="$dashboardGeoJsonMap['title']"
            :subtitle="$dashboardGeoJsonMap['subtitle']"
            :height="$dashboardGeoJsonMap['height']"
            :center="$dashboardGeoJsonMap['center']"
            :zoom="$dashboardGeoJsonMap['zoom']"
            :chips="$dashboardGeoJsonMap['chips']"
            :list-url="route('dashboard.map.countries.index')"
        />

        <div class="stats-grid dashboard-stats-grid">
            @foreach ($resourceCards as $card)
                @if (auth()->user()?->can($card['permission']))
                    <article class="stat-card dashboard-stat-card dashboard-stat-card--{{ $card['tone'] ?? 'azure' }}">
                        <div class="stat-card__head">
                            <span class="stat-icon app-icon-box app-icon-box--lg">
                                <i class="material-icons app-icon app-icon--lg" aria-hidden="true">{{ $card['icon'] }}</i>
                            </span>
                            <a class="text-link dashboard-stat-card__link" href="{{ $card['route'] }}">{{ $card['action'] }}</a>
                        </div>

                        <strong class="stat-value">{{ $card['count'] }}</strong>
                        <h2 class="stat-title">{{ $card['title'] }}</h2>
                        <p class="stat-description">{{ $card['description'] }}</p>
                    </article>
                @endif
            @endforeach
        </div>

    </div>
@endsection

@if (($eventCalendar['has_access'] ?? false) === true)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const replaceCount = (template, count, fallback = '') => {
                    if (typeof template === 'string' && template !== '') {
                        return template.replace(':count', String(count));
                    }

                    return fallback !== '' ? fallback : String(count);
                };

                const resolveCalendarItemType = (item) => item?.type ?? 'unknown';
                const resolveCalendarItemStatus = (item) => item?.status ?? null;
                const resolveCalendarItemId = (item) => String(item?.id ?? '');
                const DAY_MARKER_LIMIT = 14;

                const parseJsonScript = (scriptElement, fallback) => {
                    if (!scriptElement) {
                        return fallback;
                    }

                    try {
                        const parsed = JSON.parse(scriptElement.textContent || '');

                        return parsed ?? fallback;
                    } catch (error) {
                        console.error('Calendar JSON parse failed.', error);

                        return fallback;
                    }
                };

                const parseIsoDate = (isoDate) => {
                    if (typeof isoDate !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(isoDate)) {
                        return null;
                    }

                    const [year, month, day] = isoDate.split('-').map(Number);
                    const utcDate = new Date(Date.UTC(year, month - 1, day));

                    return Number.isNaN(utcDate.getTime()) ? null : utcDate;
                };

                const formatIsoDate = (date) => {
                    if (!date || Number.isNaN(date.getTime())) {
                        return '';
                    }

                    return [
                        date.getUTCFullYear(),
                        String(date.getUTCMonth() + 1).padStart(2, '0'),
                        String(date.getUTCDate()).padStart(2, '0'),
                    ].join('-');
                };

                const addDaysToIsoDate = (isoDate, days = 1) => {
                    const date = parseIsoDate(isoDate);

                    if (!date) {
                        return '';
                    }

                    date.setUTCDate(date.getUTCDate() + days);

                    return formatIsoDate(date);
                };

                const parseMonthKey = (monthKey) => {
                    if (typeof monthKey !== 'string' || !/^\d{4}-\d{2}$/.test(monthKey)) {
                        return null;
                    }

                    const date = parseIsoDate(`${monthKey}-01`);

                    if (!date) {
                        return null;
                    }

                    return {
                        year: String(date.getUTCFullYear()),
                        month: String(date.getUTCMonth() + 1).padStart(2, '0'),
                    };
                };

                const buildMonthKey = (year, month) => {
                    if (year === '' || month === '') {
                        return '';
                    }

                    return `${String(year)}-${String(month).padStart(2, '0')}`;
                };

                const normalizeMonthOptions = (monthOptions, fallback = {}) => {
                    const source = monthOptions && typeof monthOptions === 'object' ? monthOptions : fallback;

                    return Object.entries(source)
                        .map(([value, label]) => ({
                            value: String(value).padStart(2, '0'),
                            label: String(label),
                        }))
                        .sort((left, right) => Number(left.value) - Number(right.value));
                };

                const normalizeYearOptions = (yearOptions, fallback = []) => {
                    const source = Array.isArray(yearOptions) && yearOptions.length > 0 ? yearOptions : fallback;

                    return source.map((year) => String(year));
                };

                const buildBaseDayLookup = (calendar, monthLabel) => {
                    const rawLookup = calendar?.day_lookup && typeof calendar.day_lookup === 'object'
                        ? calendar.day_lookup
                        : {};
                    const lookup = {};

                    Object.entries(rawLookup).forEach(([date, rawDay]) => {
                        const items = Array.isArray(rawDay?.items) ? rawDay.items : [];

                        lookup[date] = {
                            date,
                            label: rawDay?.label || date || monthLabel || '',
                            is_current_month: Boolean(rawDay?.is_current_month),
                            item_count: Number(rawDay?.item_count ?? items.length ?? 0),
                            items,
                        };
                    });

                    (Array.isArray(calendar?.weeks) ? calendar.weeks : []).forEach((week) => {
                        (Array.isArray(week?.days) ? week.days : []).forEach((day) => {
                            const date = day?.date || '';

                            if (date === '' || lookup[date]) {
                                return;
                            }

                            lookup[date] = {
                                date,
                                label: day?.label || date || monthLabel || '',
                                is_current_month: Boolean(day?.is_current_month),
                                item_count: Number(day?.item_count ?? 0),
                                items: [],
                            };
                        });
                    });

                    return lookup;
                };

                const createIcon = (name) => {
                    const icon = document.createElement('i');
                    icon.className = 'material-icons app-icon app-icon--sm';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.textContent = name;

                    return icon;
                };

                const createPreviewMarker = (item) => {
                    const marker = document.createElement('span');
                    const itemType = resolveCalendarItemType(item);
                    marker.className = `event-calendar-compact__marker event-calendar-compact__marker--${itemType}`;
                    marker.setAttribute('role', 'link');
                    marker.tabIndex = 0;
                    marker.dataset.calendarMarkerUrl = item.url || '';
                    marker.title = item.tooltip || '';
                    marker.setAttribute('aria-label', item.title || '');
                    marker.dataset.calendarItemType = itemType;

                    const dot = document.createElement('span');
                    dot.className = 'event-calendar-compact__marker-dot';
                    dot.setAttribute('aria-hidden', 'true');
                    marker.append(dot);

                    return marker;
                };

                const createDayButton = (day, countTemplate, emptyCountLabel, moreTemplate) => {
                    const button = document.createElement('button');
                    const buttonClasses = ['event-calendar-compact__day'];

                    if (!day.is_current_month) {
                        buttonClasses.push('is-muted');
                    }

                    if (day.is_today) {
                        buttonClasses.push('is-today');
                    }

                    if (day.is_selected) {
                        buttonClasses.push('is-selected');
                    }

                    if ((day.item_count ?? 0) > 0) {
                        buttonClasses.push('has-items');
                    }

                    button.type = 'button';
                    button.className = buttonClasses.join(' ');
                    button.dataset.calendarDay = '';
                    button.dataset.date = day.date;
                    button.dataset.currentMonth = day.is_current_month ? 'true' : 'false';
                    button.setAttribute(
                        'aria-label',
                        `${day.label}, ${(day.item_count ?? 0) > 0 ? replaceCount(countTemplate, day.item_count) : emptyCountLabel}`
                    );
                    button.setAttribute('aria-pressed', day.is_selected ? 'true' : 'false');

                    const head = document.createElement('span');
                    head.className = 'event-calendar-compact__day-head';

                    const time = document.createElement('time');
                    time.dateTime = day.date;
                    time.textContent = String(day.day_number ?? '');

                    const count = document.createElement('span');
                    count.className = 'event-calendar-compact__day-count';
                    count.dataset.dayCount = '';
                    count.textContent = String(day.item_count ?? 0);
                    count.hidden = (day.item_count ?? 0) === 0;

                    head.append(time, count);

                    const preview = document.createElement('span');
                    preview.className = 'event-calendar-compact__items event-calendar-compact__markers';
                    preview.dataset.dayPreview = '';
                    (Array.isArray(day.preview_items) ? day.preview_items : []).forEach((item) => {
                        preview.append(createPreviewMarker(item));
                    });

                    const more = document.createElement('span');
                    more.className = 'event-calendar-compact__more';
                    more.dataset.dayMore = '';
                    more.hidden = (day.hidden_count ?? 0) === 0;
                    more.textContent = (day.hidden_count ?? 0) > 0
                        ? replaceCount(moreTemplate, day.hidden_count)
                        : '';

                    button.append(head, preview, more);

                    return button;
                };

                const createSpanSegment = (segment) => {
                    const link = document.createElement('a');
                    const itemType = resolveCalendarItemType(segment);
                    const classes = [
                        'event-calendar-compact__span',
                        `event-calendar-compact__span--${segment.tone}`,
                        `${itemType}-span`,
                        segment.starts_before ? 'is-continued-left' : 'is-start',
                        segment.ends_after ? 'is-continued-right' : 'is-end',
                    ];

                    link.className = classes.join(' ');
                    link.href = segment.url || '#';
                    link.title = segment.tooltip || '';
                    link.style.gridColumn = `${segment.start_column} / span ${segment.span}`;
                    link.dataset.calendarSpan = '';
                    link.dataset.itemId = resolveCalendarItemId(segment);
                    link.dataset.type = itemType;
                    link.dataset.status = resolveCalendarItemStatus(segment) ?? '';

                    const prefix = document.createElement('span');
                    prefix.className = 'event-calendar-compact__span-prefix';
                    prefix.append(createIcon(segment.icon));

                    const title = document.createElement('span');
                    title.className = 'event-calendar-compact__span-title';
                    title.textContent = segment.title || '';

                    const meta = document.createElement('span');
                    meta.className = 'event-calendar-compact__span-meta';
                    meta.textContent = segment.status_label || segment.type_label || '';

                    link.append(prefix, title, meta);

                    return link;
                };

                const createWeekSection = (week, module) => {
                    const section = document.createElement('section');
                    const lanes = Array.isArray(week?.span_lanes) ? week.span_lanes : [];
                    const laneCount = lanes.length;
                    const laneHeight = laneCount > 0 ? (laneCount * 32) + ((laneCount - 1) * 6) : 0;

                    section.className = 'event-calendar-compact__week';
                    section.style.setProperty('--week-lane-height', `${laneHeight}px`);

                    const days = document.createElement('div');
                    days.className = 'event-calendar-compact__days';

                    (Array.isArray(week?.days) ? week.days : []).forEach((day) => {
                        days.append(
                            createDayButton(
                                day,
                                module.dataset.countTemplate || '',
                                module.dataset.emptyCountLabel || '',
                                module.dataset.moreTemplate || ''
                            )
                        );
                    });

                    section.append(days);

                    if (laneCount > 0) {
                        const lanesWrap = document.createElement('div');
                        lanesWrap.className = 'event-calendar-compact__lanes';

                        lanes.forEach((laneSegments) => {
                            const lane = document.createElement('div');
                            lane.className = 'event-calendar-compact__lane';
                            lane.dataset.calendarLane = '';

                            (Array.isArray(laneSegments) ? laneSegments : []).forEach((segment) => {
                                lane.append(createSpanSegment(segment));
                            });

                            lanesWrap.append(lane);
                        });

                        section.append(lanesWrap);
                    }

                    return section;
                };

                const createDetailItem = (item) => {
                    const itemType = resolveCalendarItemType(item);
                    const link = document.createElement('a');
                    link.className = `event-calendar-compact__detail-item event-calendar-compact__detail-item--${item.tone}`;
                    link.dataset.calendarItemType = itemType;
                    link.dataset.status = resolveCalendarItemStatus(item) ?? '';
                    link.href = item.url || '#';

                    const surface = document.createElement('div');
                    surface.className = 'event-calendar-compact__detail-surface';

                    const tags = document.createElement('div');
                    tags.className = 'event-calendar-compact__detail-tags';

                    const kindTag = document.createElement('span');
                    kindTag.className = `event-calendar-compact__detail-tag event-calendar-compact__detail-tag--type event-calendar-compact__detail-tag--type-${itemType} ${itemType}-chip`;
                    kindTag.append(createIcon(item.icon));
                    const kindLabel = document.createElement('span');
                    kindLabel.textContent = item.type_label;
                    kindTag.append(kindLabel);

                    const durationTag = document.createElement('span');
                    durationTag.className = 'event-calendar-compact__detail-tag event-calendar-compact__detail-tag--duration';
                    durationTag.textContent = item.duration_label;

                    tags.append(kindTag);

                    if (item.status_label) {
                        const stateTag = document.createElement('span');
                        stateTag.className = `event-calendar-compact__detail-tag event-calendar-compact__detail-tag--status event-calendar-compact__detail-tag--status-${resolveCalendarItemStatus(item) ?? 'unknown'}`;
                        stateTag.textContent = item.status_label;
                        tags.append(stateTag);
                    }

                    tags.append(durationTag);

                    const title = document.createElement('strong');
                    title.className = 'event-calendar-compact__detail-item-title';
                    title.textContent = item.title;

                    const meta = document.createElement('div');
                    meta.className = 'event-calendar-compact__detail-meta';

                    const schedule = document.createElement('span');
                    schedule.append(createIcon(item.schedule_icon));
                    const scheduleText = document.createElement('span');
                    scheduleText.textContent = item.schedule;
                    schedule.append(scheduleText);
                    meta.append(schedule);

                    if (item.meta) {
                        const location = document.createElement('span');
                        location.append(createIcon('public'));
                        const locationText = document.createElement('span');
                        locationText.textContent = item.meta;
                        location.append(locationText);
                        meta.append(location);
                    }

                    surface.append(tags, title, meta);
                    link.append(surface);

                    const arrow = createIcon('open_in_new');
                    arrow.classList.add('event-calendar-compact__detail-arrow');
                    link.append(arrow);

                    return link;
                };

                document.querySelectorAll('[data-calendar-card]').forEach((calendarCard) => {
                    const module = calendarCard.querySelector('[data-calendar-module]');
                    const payloadScript = module?.querySelector('[data-calendar-payload]');
                    const periodOptionsScript = module?.querySelector('[data-calendar-period-options]');
                    const form = calendarCard.querySelector('[data-calendar-period-form]');
                    const hiddenInput = form?.querySelector('[data-calendar-period-value]');
                    const monthSelect = form?.querySelector('[data-calendar-period-month]');
                    const yearSelect = form?.querySelector('[data-calendar-period-year]');
                    const prevNav = calendarCard.querySelector('[data-calendar-nav="prev"]');
                    const nextNav = calendarCard.querySelector('[data-calendar-nav="next"]');
                    const loading = calendarCard.querySelector('[data-calendar-loading]');
                    const feedback = calendarCard.querySelector('[data-calendar-feedback]');
                    const endpoint = calendarCard.dataset.calendarEndpoint || form?.action || '';
                    const initialPeriodOptions = parseJsonScript(periodOptionsScript, {});
                    let eventCalendar = parseJsonScript(payloadScript, {});
                    let monthOptions = initialPeriodOptions?.months ?? {};
                    let yearOptions = Array.isArray(initialPeriodOptions?.years) ? initialPeriodOptions.years : [];
                    let calendarItems = Array.isArray(eventCalendar?.items) ? eventCalendar.items : [];
                    let dayButtons = Array.from(module?.querySelectorAll('[data-calendar-day]') ?? []);
                    let spanLanes = Array.from(module?.querySelectorAll('[data-calendar-lane]') ?? []);
                    const typeFilterButtons = Array.from(calendarCard.querySelectorAll('[data-calendar-filter-group="type"]'));
                    const statusFilterButtons = Array.from(calendarCard.querySelectorAll('[data-calendar-filter-group="status"]'));
                    const summaryCards = Array.from(calendarCard.querySelectorAll('[data-calendar-summary-key]'));
                    const totalCountBadge = calendarCard.querySelector('[data-calendar-total-count]');
                    const weekdaysContainer = module?.querySelector('.event-calendar-compact__weekdays');
                    const weeksContainer = module?.querySelector('.event-calendar-compact__weeks');
                    const detailDate = module?.querySelector('[data-calendar-detail-date]');
                    const detailCount = module?.querySelector('[data-calendar-detail-count]');
                    const detailBody = module?.querySelector('[data-calendar-detail-body]');
                    const detailEmpty = module?.querySelector('[data-calendar-detail-empty]');
                    const listingLink = calendarCard.querySelector('[data-calendar-listing-link]');
                    const listingLabel = calendarCard.querySelector('[data-calendar-listing-label]');

                    if (
                        !module
                        || !payloadScript
                        || !periodOptionsScript
                        || !form
                        || !hiddenInput
                        || !monthSelect
                        || !yearSelect
                        || !prevNav
                        || !nextNav
                        || !weekdaysContainer
                        || !weeksContainer
                        || !detailDate
                        || !detailCount
                        || !detailBody
                        || !detailEmpty
                    ) {
                        return;
                    }

                    let baseDayLookup = buildBaseDayLookup(eventCalendar, eventCalendar?.month_label || module.dataset.monthLabel || '');

                    const createEmptyDayEntry = (date) => ({
                        date,
                        label: baseDayLookup[date]?.label || date || module.dataset.monthLabel || '',
                        is_current_month: baseDayLookup[date]?.is_current_month ?? false,
                        item_count: 0,
                        items: [],
                    });

                    let activeTypeFilter = 'all';
                    let activeStatusFilter = 'all';
                    let selectedDate = module.dataset.selectedDate || dayButtons[0]?.dataset.date || '';
                    let filteredItems = calendarItems;
                    let filteredDayLookup = {};
                    let filteredItemIdSet = new Set(
                        calendarItems
                            .map((item) => resolveCalendarItemId(item))
                            .filter(Boolean)
                    );
                    let isLoading = false;
                    let requestVersion = 0;
                    let pendingRequest = null;

                    const isDefaultFilterState = () => activeTypeFilter === 'all' && activeStatusFilter === 'all';

                    const setFeedback = (message = '') => {
                        if (!feedback) {
                            return;
                        }

                        feedback.hidden = message === '';
                        feedback.textContent = message;
                    };

                    const setLoadingState = (busy) => {
                        isLoading = busy;
                        calendarCard.classList.toggle('is-loading', busy);
                        calendarCard.setAttribute('aria-busy', busy ? 'true' : 'false');

                        if (loading) {
                            loading.hidden = !busy;
                        }

                        [prevNav, nextNav].forEach((link) => {
                            link.classList.toggle('is-disabled', busy);
                            link.setAttribute('aria-disabled', busy ? 'true' : 'false');
                        });

                        [monthSelect, yearSelect].forEach((select) => {
                            select.disabled = busy;
                        });
                    };

                    const matchesTypeFilter = (item, typeFilter = activeTypeFilter) => typeFilter === 'all'
                        || resolveCalendarItemType(item) === typeFilter;

                    const matchesStatusFilter = (item, statusFilter = activeStatusFilter) => statusFilter === 'all'
                        || resolveCalendarItemStatus(item) === statusFilter;

                    const matchesActiveFilters = (item) => matchesTypeFilter(item) && matchesStatusFilter(item);

                    const buildFilteredDayLookup = (items) => {
                        const groupedItems = Object.keys(baseDayLookup).reduce((lookup, date) => {
                            lookup[date] = createEmptyDayEntry(date);

                            return lookup;
                        }, {});

                        items.forEach((item) => {
                            const startDate = item.start_date;
                            const endDate = item.end_date || item.start_date;

                            if (!startDate || !endDate || startDate > endDate) {
                                return;
                            }

                            let cursor = startDate;

                            while (cursor && cursor <= endDate) {
                                groupedItems[cursor] ||= createEmptyDayEntry(cursor);
                                groupedItems[cursor].items.push(item);
                                groupedItems[cursor].item_count = groupedItems[cursor].items.length;

                                const nextCursor = addDaysToIsoDate(cursor, 1);

                                if (!nextCursor || nextCursor <= cursor) {
                                    break;
                                }

                                cursor = nextCursor;
                            }
                        });

                        return groupedItems;
                    };

                    const syncInteractiveRefs = () => {
                        dayButtons = Array.from(module.querySelectorAll('[data-calendar-day]'));
                        spanLanes = Array.from(module.querySelectorAll('[data-calendar-lane]'));
                    };

                    const renderCalendarFrame = () => {
                        const weekdayCells = (Array.isArray(eventCalendar?.day_labels) ? eventCalendar.day_labels : []).map((label) => {
                            const cell = document.createElement('div');
                            cell.className = 'event-calendar-compact__weekday';
                            cell.textContent = label;

                            return cell;
                        });

                        weekdaysContainer.replaceChildren(...weekdayCells);

                        const weekSections = (Array.isArray(eventCalendar?.weeks) ? eventCalendar.weeks : []).map((week) => {
                            return createWeekSection(week, module);
                        });

                        weeksContainer.replaceChildren(...weekSections);
                        syncInteractiveRefs();
                    };

                    const refreshFilteredState = () => {
                        filteredItems = calendarItems.filter((item) => matchesActiveFilters(item));
                        filteredDayLookup = buildFilteredDayLookup(filteredItems);
                        filteredItemIdSet = new Set(
                            filteredItems
                                .map((item) => resolveCalendarItemId(item))
                                .filter(Boolean)
                        );
                    };

                    const filteredItemsForDate = (date) => {
                        if (!date) {
                            return [];
                        }

                        const itemsForDate = filteredDayLookup[date]?.items;

                        return Array.isArray(itemsForDate) ? itemsForDate : [];
                    };

                    const countItems = (predicate) => calendarItems.filter((item) => predicate(item)).length;

                    const countForTypeFilter = (typeValue) => countItems((item) => {
                        return resolveCalendarItemType(item) === typeValue && matchesStatusFilter(item);
                    });

                    const countForStatusFilter = (statusValue) => countItems((item) => {
                        if (!matchesTypeFilter(item)) {
                            return false;
                        }

                        return statusValue === 'all'
                            ? true
                            : resolveCalendarItemStatus(item) === statusValue;
                    });

                    const syncFilterButtons = () => {
                        typeFilterButtons.forEach((button) => {
                            const value = button.dataset.calendarFilterValue || button.dataset.calendarFilter || '';
                            const isActive = activeTypeFilter === value;
                            const count = button.querySelector('[data-calendar-filter-count]');

                            button.classList.toggle('is-active', isActive);
                            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');

                            if (count) {
                                count.textContent = String(countForTypeFilter(value));
                            }
                        });

                        statusFilterButtons.forEach((button) => {
                            const value = button.dataset.calendarFilterValue || button.dataset.calendarFilter || 'all';
                            const isActive = activeStatusFilter === value;
                            const count = button.querySelector('[data-calendar-filter-count]');

                            button.classList.toggle('is-active', isActive);
                            button.setAttribute('aria-checked', isActive ? 'true' : 'false');

                            if (count) {
                                count.textContent = String(countForStatusFilter(value));
                            }
                        });
                    };

                    const syncSummary = () => {
                        const summaryCounts = {
                            events: filteredItems.filter((item) => resolveCalendarItemType(item) === 'event').length,
                            ongoing: filteredItems.filter((item) => resolveCalendarItemStatus(item) === 'ongoing').length,
                            planned: filteredItems.filter((item) => resolveCalendarItemStatus(item) === 'planned').length,
                            visits: filteredItems.filter((item) => resolveCalendarItemType(item) === 'visit').length,
                            birthdays: filteredItems.filter((item) => resolveCalendarItemType(item) === 'birthday').length,
                        };

                        if (totalCountBadge) {
                            totalCountBadge.textContent = replaceCount(module.dataset.countTemplate, filteredItems.length);
                        }

                        summaryCards.forEach((card) => {
                            const key = card.dataset.calendarSummaryKey || '';
                            const count = card.querySelector('[data-calendar-summary-count]');

                            if (count && key in summaryCounts) {
                                count.textContent = String(summaryCounts[key]);
                            }
                        });
                    };

                    const syncPeriodControls = () => {
                        const monthKey = parseMonthKey(eventCalendar?.month_key || hiddenInput.value || '');
                        const normalizedMonthOptions = normalizeMonthOptions(monthOptions);
                        const normalizedYearOptions = normalizeYearOptions(
                            yearOptions,
                            Array.from(yearSelect.options).map((option) => option.value)
                        );

                        hiddenInput.value = eventCalendar?.month_key || hiddenInput.value;
                        form.setAttribute('aria-label', eventCalendar?.month_label || form.getAttribute('aria-label') || '');

                        monthSelect.replaceChildren(...normalizedMonthOptions.map((option) => {
                            const element = document.createElement('option');
                            element.value = option.value;
                            element.textContent = option.label;

                            return element;
                        }));

                        yearSelect.replaceChildren(...normalizedYearOptions.map((year) => {
                            const element = document.createElement('option');
                            element.value = year;
                            element.textContent = year;

                            return element;
                        }));

                        if (monthKey) {
                            monthSelect.value = monthKey.month;
                            yearSelect.value = monthKey.year;
                        }

                        prevNav.href = eventCalendar?.prev_url || prevNav.href;
                        nextNav.href = eventCalendar?.next_url || nextNav.href;

                        if (listingLink) {
                            if (eventCalendar?.listing_url) {
                                listingLink.hidden = false;
                                listingLink.href = eventCalendar.listing_url;
                            } else {
                                listingLink.hidden = true;
                            }
                        }

                        if (listingLabel && eventCalendar?.listing_label) {
                            listingLabel.textContent = eventCalendar.listing_label;
                        }
                    };

                    const updateBrowserMonth = (monthKey) => {
                        if (typeof window === 'undefined' || monthKey === '') {
                            return;
                        }

                        const url = new URL(window.location.href);
                        url.searchParams.set('month', monthKey);
                        window.history.replaceState({calendarMonth: monthKey}, '', url);
                    };

                    const resolvePreservedSelectedDate = () => {
                        if (selectedDate && baseDayLookup[selectedDate]) {
                            return selectedDate;
                        }

                        if (selectedDate) {
                            const monthKey = parseMonthKey(eventCalendar?.month_key || '');
                            const daySegment = selectedDate.slice(8, 10);

                            if (monthKey && daySegment !== '') {
                                const candidate = `${monthKey.year}-${monthKey.month}-${daySegment}`;

                                if (baseDayLookup[candidate]) {
                                    return candidate;
                                }
                            }
                        }

                        return eventCalendar?.selected_date || Object.keys(baseDayLookup)[0] || '';
                    };

                    const applyCalendarPayload = (payload, {updateHistory = false} = {}) => {
                        eventCalendar = payload?.eventCalendar && typeof payload.eventCalendar === 'object'
                            ? payload.eventCalendar
                            : {};
                        monthOptions = payload?.calendarMonthOptions ?? payload?.monthOptions ?? monthOptions;
                        yearOptions = Array.isArray(payload?.calendarYearOptions ?? payload?.yearOptions)
                            ? (payload.calendarYearOptions ?? payload.yearOptions)
                            : yearOptions;
                        calendarItems = Array.isArray(eventCalendar?.items) ? eventCalendar.items : [];
                        baseDayLookup = buildBaseDayLookup(eventCalendar, eventCalendar?.month_label || module.dataset.monthLabel || '');

                        module.dataset.countTemplate = eventCalendar?.texts?.item_count || module.dataset.countTemplate || '';
                        module.dataset.emptyCountLabel = eventCalendar?.texts?.empty_count || module.dataset.emptyCountLabel || '';
                        module.dataset.moreTemplate = eventCalendar?.texts?.more_items || module.dataset.moreTemplate || '';
                        module.dataset.detailEmpty = eventCalendar?.texts?.detail_empty || module.dataset.detailEmpty || '';
                        module.dataset.detailFilterEmpty = eventCalendar?.texts?.empty_filtered || module.dataset.detailFilterEmpty || '';
                        module.dataset.monthLabel = eventCalendar?.month_label || module.dataset.monthLabel || '';

                        renderCalendarFrame();
                        selectedDate = resolvePreservedSelectedDate();
                        refreshFilteredState();
                        syncPeriodControls();
                        syncFilterButtons();
                        syncSummary();
                        renderDayPreviews();
                        renderSpanBars();
                        renderDetail();

                        payloadScript.textContent = JSON.stringify(eventCalendar);
                        periodOptionsScript.textContent = JSON.stringify({
                            months: monthOptions,
                            years: yearOptions,
                        });

                        if (updateHistory) {
                            updateBrowserMonth(eventCalendar?.month_key || '');
                        }
                    };

                    const resolveSelectedDate = () => {
                        if (selectedDate && filteredItemsForDate(selectedDate).length > 0) {
                            return selectedDate;
                        }

                        const currentMonthBusyDay = dayButtons.find((button) => button.dataset.currentMonth === 'true' && filteredItemsForDate(button.dataset.date).length > 0);

                        if (currentMonthBusyDay) {
                            return currentMonthBusyDay.dataset.date;
                        }

                        const anyBusyDay = dayButtons.find((button) => filteredItemsForDate(button.dataset.date).length > 0);

                        if (anyBusyDay) {
                            return anyBusyDay.dataset.date;
                        }

                        return selectedDate || eventCalendar?.selected_date || dayButtons[0]?.dataset.date || '';
                    };

                    const renderDayPreviews = () => {
                        dayButtons.forEach((button) => {
                            const date = button.dataset.date;
                            const dayEntry = filteredDayLookup[date] ?? createEmptyDayEntry(date);
                            const items = Array.isArray(dayEntry.items) ? dayEntry.items : [];
                            const startItems = items.filter((item) => item.start_date === date);
                            const preview = button.querySelector('[data-day-preview]');
                            const more = button.querySelector('[data-day-more]');
                            const count = button.querySelector('[data-day-count]');
                            const label = dayEntry.label || date;

                            if (!preview || !more || !count) {
                                return;
                            }

                            preview.replaceChildren();
                            startItems.slice(0, DAY_MARKER_LIMIT).forEach((item) => preview.append(createPreviewMarker(item)));

                            if (startItems.length > DAY_MARKER_LIMIT) {
                                more.hidden = false;
                                more.textContent = replaceCount(module.dataset.moreTemplate, startItems.length - DAY_MARKER_LIMIT);
                            } else {
                                more.hidden = true;
                                more.textContent = '';
                            }

                            if (items.length > 0) {
                                count.hidden = false;
                                count.textContent = items.length;
                                button.classList.add('has-items');
                            } else {
                                count.hidden = true;
                                count.textContent = '0';
                                button.classList.remove('has-items');
                            }

                            button.setAttribute(
                                'aria-label',
                                `${label}, ${items.length > 0 ? replaceCount(module.dataset.countTemplate, items.length) : module.dataset.emptyCountLabel}`
                            );
                        });
                    };

                    const renderSpanBars = () => {
                        spanLanes.forEach((lane) => {
                            let hasVisibleItems = false;

                            lane.querySelectorAll('[data-calendar-span]').forEach((span) => {
                                const itemId = span.dataset.itemId || '';
                                const fallbackSpanItem = {
                                    type: span.dataset.type || 'unknown',
                                    status: span.dataset.status || null,
                                };
                                const isVisible = itemId !== ''
                                    ? filteredItemIdSet.has(itemId)
                                    : matchesActiveFilters(fallbackSpanItem);

                                span.hidden = !isVisible;
                                hasVisibleItems ||= isVisible;
                            });

                            lane.hidden = !hasVisibleItems;
                        });
                    };

                    const renderDetail = () => {
                        selectedDate = resolveSelectedDate();
                        module.dataset.selectedDate = selectedDate;

                        dayButtons.forEach((button) => {
                            const isSelected = button.dataset.date === selectedDate;
                            button.classList.toggle('is-selected', isSelected);
                            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                        });

                        const selectedDay = selectedDate && (filteredDayLookup[selectedDate] || baseDayLookup[selectedDate])
                            ? (filteredDayLookup[selectedDate] || baseDayLookup[selectedDate])
                            : createEmptyDayEntry(selectedDate || dayButtons[0]?.dataset.date || '');
                        const items = filteredItemsForDate(selectedDate);

                        detailDate.textContent = selectedDay.label || selectedDate || module.dataset.monthLabel || '';
                        detailCount.textContent = items.length > 0
                            ? replaceCount(module.dataset.countTemplate, items.length)
                            : module.dataset.emptyCountLabel;

                        detailBody.replaceChildren();

                        if (items.length === 0) {
                            detailEmpty.hidden = false;
                            detailEmpty.textContent = isDefaultFilterState()
                                ? module.dataset.detailEmpty
                                : module.dataset.detailFilterEmpty;
                            return;
                        }

                        detailEmpty.hidden = true;
                        items.forEach((item) => detailBody.append(createDetailItem(item)));
                    };

                    const requestCalendar = async (monthKey) => {
                        if (monthKey === '' || monthKey === (eventCalendar?.month_key || '') || isLoading) {
                            return;
                        }

                        requestVersion += 1;
                        const currentRequest = requestVersion;

                        if (pendingRequest) {
                            pendingRequest.abort();
                        }

                        pendingRequest = new AbortController();
                        setFeedback('');
                        setLoadingState(true);

                        try {
                            const url = new URL(endpoint, window.location.origin);
                            url.searchParams.set('month', monthKey);

                            const response = await fetch(url.toString(), {
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                                signal: pendingRequest.signal,
                            });

                            if (!response.ok) {
                                throw new Error(`Calendar request failed with status ${response.status}`);
                            }

                            const payload = await response.json();

                            if (currentRequest !== requestVersion) {
                                return;
                            }

                            applyCalendarPayload(payload, {updateHistory: true});
                        } catch (error) {
                            if (error?.name === 'AbortError') {
                                return;
                            }

                            console.error('Calendar async update failed.', error);
                            setFeedback("Kalendar ma'lumotlarini yangilab bo'lmadi. Qayta urinib ko'ring.");
                        } finally {
                            if (currentRequest === requestVersion) {
                                setLoadingState(false);
                            }
                        }
                    };

                    [...typeFilterButtons, ...statusFilterButtons].forEach((button) => {
                        button.addEventListener('click', () => {
                            const group = button.dataset.calendarFilterGroup || 'all';
                            const value = button.dataset.calendarFilterValue || button.dataset.calendarFilter || 'all';

                            if (group === 'type') {
                                activeTypeFilter = activeTypeFilter === value ? 'all' : value;
                            }

                            if (group === 'status') {
                                activeStatusFilter = value;
                            }

                            refreshFilteredState();
                            syncFilterButtons();
                            syncSummary();
                            renderDayPreviews();
                            renderSpanBars();
                            renderDetail();
                        });
                    });

                    weeksContainer.addEventListener('click', (event) => {
                        const marker = event.target.closest('[data-calendar-marker-url]');

                        if (marker && weeksContainer.contains(marker)) {
                            event.preventDefault();
                            event.stopPropagation();
                            const url = marker.getAttribute('data-calendar-marker-url') || '';

                            if (url !== '') {
                                window.location.assign(url);
                            }

                            return;
                        }

                        const button = event.target.closest('[data-calendar-day]');

                        if (!button || !weeksContainer.contains(button)) {
                            return;
                        }

                        selectedDate = button.dataset.date || selectedDate;
                        renderDetail();
                    });

                    weeksContainer.addEventListener('keydown', (event) => {
                        const marker = event.target.closest('[data-calendar-marker-url]');

                        if (!marker || !weeksContainer.contains(marker)) {
                            return;
                        }

                        if (event.key !== 'Enter' && event.key !== ' ') {
                            return;
                        }

                        event.preventDefault();
                        event.stopPropagation();
                        const url = marker.getAttribute('data-calendar-marker-url') || '';

                        if (url !== '') {
                            window.location.assign(url);
                        }
                    });

                    form.addEventListener('submit', (event) => {
                        event.preventDefault();
                        requestCalendar(buildMonthKey(yearSelect.value, monthSelect.value));
                    });

                    monthSelect.addEventListener('change', () => {
                        requestCalendar(buildMonthKey(yearSelect.value, monthSelect.value));
                    });

                    yearSelect.addEventListener('change', () => {
                        requestCalendar(buildMonthKey(yearSelect.value, monthSelect.value));
                    });

                    [prevNav, nextNav].forEach((link) => {
                        link.addEventListener('click', (event) => {
                            event.preventDefault();

                            if (isLoading) {
                                return;
                            }

                            const targetUrl = new URL(link.href, window.location.origin);
                            requestCalendar(targetUrl.searchParams.get('month') || '');
                        });
                    });

                    applyCalendarPayload({
                        eventCalendar,
                        calendarMonthOptions: monthOptions,
                        calendarYearOptions: yearOptions,
                    });
                });
            });
        </script>
    @endpush
@endif

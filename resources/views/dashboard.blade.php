@extends('layouts.dashboard')

@section('title', __('ui.dashboard.page_title'))

@section('content')
    @php
        $currentRole = auth()->user()?->getRoleNames()->first();
        $translatedRole = $currentRole ? __("ui.roles.$currentRole") : __('ui.roles.unassigned');
        $roleLabel = $currentRole && $translatedRole === "ui.roles.$currentRole"
            ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $currentRole))
            : $translatedRole;
        $resourceCards = [
            [
                'permission' => 'view users',
                'title' => __('ui.dashboard.cards.users.title'),
                'count' => \App\Models\User::count(),
                'description' => __('ui.dashboard.cards.users.description'),
                'icon' => 'groups',
                'route' => route('users.index'),
                'action' => __('ui.dashboard.cards.users.action'),
            ],
            [
                'permission' => 'view departments',
                'title' => __('ui.dashboard.cards.departments.title'),
                'count' => \App\Models\Department::count(),
                'description' => __('ui.dashboard.cards.departments.description'),
                'icon' => 'apartment',
                'route' => route('departments.index'),
                'action' => __('ui.dashboard.cards.departments.action'),
            ],
            [
                'permission' => 'view ranks',
                'title' => __('ui.dashboard.cards.ranks.title'),
                'count' => \App\Models\Rank::count(),
                'description' => __('ui.dashboard.cards.ranks.description'),
                'icon' => 'military_tech',
                'route' => route('ranks.index'),
                'action' => __('ui.dashboard.cards.ranks.action'),
            ],
            [
                'permission' => 'view countries',
                'title' => __('ui.dashboard.cards.countries.title'),
                'count' => \App\Models\Country::count(),
                'description' => __('ui.dashboard.cards.countries.description'),
                'icon' => 'public',
                'route' => route('countries.index'),
                'action' => __('ui.dashboard.cards.countries.action'),
            ],
            [
                'permission' => 'view agreements',
                'title' => __('ui.dashboard.cards.agreements.title'),
                'count' => \App\Models\Agreement::count(),
                'description' => __('ui.dashboard.cards.agreements.description'),
                'icon' => 'feed',
                'route' => route('agreements.index'),
                'action' => __('ui.dashboard.cards.agreements.action'),
            ],
            [
                'permission' => 'view agreement types',
                'title' => __('ui.dashboard.cards.agreement_types.title'),
                'count' => \App\Models\AgreementType::count(),
                'description' => __('ui.dashboard.cards.agreement_types.description'),
                'icon' => 'description',
                'route' => route('agreement-types.index'),
                'action' => __('ui.dashboard.cards.agreement_types.action'),
            ],
            [
                'permission' => 'view agreement directions',
                'title' => __('ui.dashboard.cards.agreement_directions.title'),
                'count' => \App\Models\AgreementDirection::count(),
                'description' => __('ui.dashboard.cards.agreement_directions.description'),
                'icon' => 'alt_route',
                'route' => route('agreement-directions.index'),
                'action' => __('ui.dashboard.cards.agreement_directions.action'),
            ],
            [
                'permission' => 'view organization types',
                'title' => __('ui.dashboard.cards.organization_types.title'),
                'count' => \App\Models\OrganizationType::count(),
                'description' => __('ui.dashboard.cards.organization_types.description'),
                'icon' => 'domain',
                'route' => route('organization-types.index'),
                'action' => __('ui.dashboard.cards.organization_types.action'),
            ],
            [
                'permission' => 'view partner organizations',
                'title' => __('ui.dashboard.cards.partner_organizations.title'),
                'count' => \App\Models\PartnerOrganization::count(),
                'description' => __('ui.dashboard.cards.partner_organizations.description'),
                'icon' => 'business',
                'route' => route('partner-organizations.index'),
                'action' => __('ui.dashboard.cards.partner_organizations.action'),
            ],
            [
                'permission' => 'view partner contacts',
                'title' => __('ui.dashboard.cards.partner_contacts.title'),
                'count' => \App\Models\PartnerContact::count(),
                'description' => __('ui.dashboard.cards.partner_contacts.description'),
                'icon' => 'contact_phone',
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
                <p class="page-subtitle">
                    {{ __('ui.dashboard.subtitle') }}
                </p>
            </div>

            <div class="context-chip">
                <i class="material-icons" aria-hidden="true">shield</i>
                <span>{{ $roleLabel }}</span>
            </div>
        </div>

        <div class="stats-grid">
            @foreach ($resourceCards as $card)
                @if (auth()->user()?->can($card['permission']))
                    <article class="stat-card">
                        <div class="stat-card__head">
                            <span class="stat-icon">
                                <i class="material-icons" aria-hidden="true">{{ $card['icon'] }}</i>
                            </span>
                            <a class="text-link" href="{{ $card['route'] }}">{{ $card['action'] }}</a>
                        </div>

                        <strong class="stat-value">{{ $card['count'] }}</strong>
                        <h2 class="stat-title">{{ $card['title'] }}</h2>
                        <p class="stat-description">{{ $card['description'] }}</p>
                    </article>
                @endif
            @endforeach
        </div>

        @if (($eventCalendar['has_access'] ?? false) === true)
            @php
                $calendarTexts = $eventCalendar['texts'] ?? [];
                $selectedDay = $eventCalendar['selected_day'] ?? null;
                $calendarMonth = \Carbon\CarbonImmutable::createFromFormat('!Y-m', $eventCalendar['month_key']);
                $calendarMonthOptions = trans('ui.dashboard.calendar.months');
                if (! is_array($calendarMonthOptions) || count($calendarMonthOptions) !== 12) {
                    $calendarMonthOptions = [
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
                }
                $calendarYearOptions = range($calendarMonth->year - 5, $calendarMonth->year + 5);
            @endphp
            <section class="content-card dashboard-calendar-card">
                <div class="section-heading dashboard-calendar-card__head">
                    <div class="dashboard-calendar-card__intro">
                        <p class="eyebrow">{{ __('ui.dashboard.calendar.eyebrow') }}</p>
                        <div class="dashboard-calendar-card__title-row">
                            <h2 class="section-title">{{ __('ui.dashboard.calendar.title') }}</h2>
                            <span class="badge" data-calendar-total-count>{{ $eventCalendar['count_label'] }}</span>
                        </div>
                        <p class="dashboard-calendar-card__subtitle">
                            {{ $eventCalendar['subtitle'] }}
                        </p>
                    </div>

                    <div class="dashboard-calendar-card__controls">
                        <div class="dashboard-calendar-card__month-nav">
                            <a class="btn btn--ghost dashboard-calendar-card__nav" href="{{ $eventCalendar['prev_url'] }}" aria-label="{{ __('ui.dashboard.calendar.previous_month') }}">
                                <i class="material-icons" aria-hidden="true">chevron_left</i>
                            </a>

                            <div class="dashboard-calendar-card__month">
                                <form class="dashboard-calendar-card__period-form" method="GET" action="{{ route('dashboard') }}" data-calendar-period-form aria-label="{{ $eventCalendar['month_label'] }}">
                                    <input type="hidden" name="month" value="{{ $eventCalendar['month_key'] }}" data-calendar-period-value>

                                    <label class="dashboard-calendar-card__select-wrap">
                                        <select
                                            class="dashboard-calendar-card__select dashboard-calendar-card__select--month"
                                            aria-label="Oy tanlang"
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
                                            aria-label="Yil tanlang"
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

                            <a class="btn btn--ghost dashboard-calendar-card__nav" href="{{ $eventCalendar['next_url'] }}" aria-label="{{ __('ui.dashboard.calendar.next_month') }}">
                                <i class="material-icons" aria-hidden="true">chevron_right</i>
                            </a>
                        </div>

                        @if ($eventCalendar['listing_url'])
                            <a class="btn btn--ghost dashboard-calendar-card__link" href="{{ $eventCalendar['listing_url'] }}">
                                <i class="material-icons" aria-hidden="true">calendar_month</i>
                                <span>{{ $eventCalendar['listing_label'] }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="dashboard-calendar-card__toolbar">
                    <div class="dashboard-calendar-card__stats" aria-label="Tadbirlar moduli statistikasi">
                        @foreach ($eventCalendar['summary'] as $stat)
                            <article class="dashboard-calendar-card__stat dashboard-calendar-card__stat--{{ $stat['tone'] }}" data-calendar-summary-key="{{ $stat['key'] }}">
                                <span class="dashboard-calendar-card__stat-icon">
                                    <i class="material-icons" aria-hidden="true">{{ $stat['icon'] }}</i>
                                </span>
                                <div class="dashboard-calendar-card__stat-copy">
                                    <strong data-calendar-summary-count>{{ $stat['count'] }}</strong>
                                    <span>{{ $stat['label'] }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="dashboard-calendar-card__filter-layout" aria-label="Tadbirlar moduli filterlari">
                        <div class="dashboard-calendar-card__filter-group">
                            <div class="dashboard-calendar-card__filter-head">
                                <div>
                                    <p class="dashboard-calendar-card__filter-label">Turlar</p>
                                    <p class="dashboard-calendar-card__filter-hint">Tanlanmasa barcha turlar, tanlansa faqat o'sha tur ko'rsatiladi</p>
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
                                    <p class="dashboard-calendar-card__filter-hint">Bir vaqtning o'zida faqat bitta holat tanlanadi</p>
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

                @if (($eventCalendar['item_count'] ?? 0) > 0)
                    <div
                        class="event-calendar-compact"
                        data-calendar-module
                        data-selected-date="{{ $eventCalendar['selected_date'] }}"
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
                                        $weekLaneHeight = $weekLaneCount > 0 ? ($weekLaneCount * 30) + (($weekLaneCount - 1) * 6) : 0;
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

                                                    <span class="event-calendar-compact__items" data-day-preview>
                                                        @foreach ($day['preview_items'] as $item)
                                                            <span class="event-calendar-compact__item event-calendar-compact__item--{{ $item['tone'] }}" data-calendar-item-type="{{ $item['type'] }}" title="{{ $item['tooltip'] }}">
                                                                <i class="material-icons" aria-hidden="true">{{ $item['icon'] }}</i>
                                                                <span>{{ $item['title'] }}</span>
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
                                                                class="event-calendar-compact__span event-calendar-compact__span--{{ $segment['tone'] }} {{ $segment['starts_before'] ? 'is-continued-left' : 'is-start' }} {{ $segment['ends_after'] ? 'is-continued-right' : 'is-end' }}"
                                                                href="{{ $segment['url'] }}"
                                                                title="{{ $segment['tooltip'] }}"
                                                                style="grid-column: {{ $segment['start_column'] }} / span {{ $segment['span'] }}"
                                                                data-calendar-span
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
                                    <a class="event-calendar-compact__detail-item event-calendar-compact__detail-item--{{ $item['tone'] }}" data-calendar-item-type="{{ $item['type'] }}" href="{{ $item['url'] }}">
                                        <div class="event-calendar-compact__detail-surface">
                                            <div class="event-calendar-compact__detail-tags">
                                                <span class="event-calendar-compact__detail-tag event-calendar-compact__detail-tag--type event-calendar-compact__detail-tag--type-{{ $item['type'] }}">
                                                    <i class="material-icons" aria-hidden="true">{{ $item['icon'] }}</i>
                                                    <span>{{ $item['type_label'] }}</span>
                                                </span>
                                                @if (($item['status_label'] ?? null) !== null)
                                                    <span class="event-calendar-compact__detail-tag event-calendar-compact__detail-tag--muted">
                                                        {{ $item['status_label'] }}
                                                    </span>
                                                @endif
                                                <span class="event-calendar-compact__detail-tag event-calendar-compact__detail-tag--muted">
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
                    </div>
                @else
                    <div class="table-empty">
                        {{ $calendarTexts['empty_state'] }}
                    </div>
                @endif
            </section>
        @endif

    </div>
@endsection

@if (($eventCalendar['has_access'] ?? false) === true)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-calendar-period-form]').forEach((form) => {
                    const hiddenInput = form.querySelector('[data-calendar-period-value]');
                    const monthSelect = form.querySelector('[data-calendar-period-month]');
                    const yearSelect = form.querySelector('[data-calendar-period-year]');

                    if (!hiddenInput || !monthSelect || !yearSelect) {
                        return;
                    }

                    const submitPeriod = () => {
                        hiddenInput.value = `${yearSelect.value}-${String(monthSelect.value).padStart(2, '0')}`;
                        form.requestSubmit();
                    };

                    monthSelect.addEventListener('change', submitPeriod);
                    yearSelect.addEventListener('change', submitPeriod);
                });
            });
        </script>
    @endpush
@endif

@if (($eventCalendar['has_access'] ?? false) === true && ($eventCalendar['item_count'] ?? 0) > 0)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modules = document.querySelectorAll('[data-calendar-module]');

                const replaceCount = (template, count) => template.replace(':count', String(count));

                const resolveCalendarItemType = (item) => item.type ?? 'unknown';
                const resolveCalendarItemStatus = (item) => item.status ?? null;

                const createIcon = (name) => {
                    const icon = document.createElement('i');
                    icon.className = 'material-icons';
                    icon.setAttribute('aria-hidden', 'true');
                    icon.textContent = name;

                    return icon;
                };

                const createPreviewItem = (item) => {
                    const chip = document.createElement('span');
                    chip.className = `event-calendar-compact__item event-calendar-compact__item--${item.tone}`;
                    chip.dataset.calendarItemType = resolveCalendarItemType(item);
                    chip.title = item.tooltip;

                    chip.append(createIcon(item.icon));

                    const title = document.createElement('span');
                    title.textContent = item.title;
                    chip.append(title);

                    return chip;
                };

                const createDetailItem = (item) => {
                    const link = document.createElement('a');
                    link.className = `event-calendar-compact__detail-item event-calendar-compact__detail-item--${item.tone}`;
                    link.dataset.calendarItemType = resolveCalendarItemType(item);
                    link.href = item.url;

                    const surface = document.createElement('div');
                    surface.className = 'event-calendar-compact__detail-surface';

                    const tags = document.createElement('div');
                    tags.className = 'event-calendar-compact__detail-tags';

                    const kindTag = document.createElement('span');
                    kindTag.className = `event-calendar-compact__detail-tag event-calendar-compact__detail-tag--type event-calendar-compact__detail-tag--type-${resolveCalendarItemType(item)}`;
                    kindTag.append(createIcon(item.icon));
                    const kindLabel = document.createElement('span');
                    kindLabel.textContent = item.type_label;
                    kindTag.append(kindLabel);

                    const durationTag = document.createElement('span');
                    durationTag.className = 'event-calendar-compact__detail-tag event-calendar-compact__detail-tag--muted';
                    durationTag.textContent = item.duration_label;

                    tags.append(kindTag);

                    if (item.status_label) {
                        const stateTag = document.createElement('span');
                        stateTag.className = 'event-calendar-compact__detail-tag event-calendar-compact__detail-tag--muted';
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

                modules.forEach((module) => {
                    const script = module.querySelector('[data-calendar-day-lookup]');
                    const itemsScript = module.querySelector('[data-calendar-items]');

                    if (!script || !itemsScript) {
                        return;
                    }

                    const dayLookup = JSON.parse(script.textContent || '{}');
                    const calendarItems = JSON.parse(itemsScript.textContent || '[]');
                    const calendarCard = module.closest('.dashboard-calendar-card');
                    const dayButtons = Array.from(module.querySelectorAll('[data-calendar-day]'));
                    const spanLanes = Array.from(module.querySelectorAll('[data-calendar-lane]'));
                    const typeFilterButtons = Array.from(calendarCard.querySelectorAll('[data-calendar-filter-group="type"]'));
                    const statusFilterButtons = Array.from(calendarCard.querySelectorAll('[data-calendar-filter-group="status"]'));
                    const filterButtons = [...typeFilterButtons, ...statusFilterButtons];
                    const summaryCards = Array.from(calendarCard.querySelectorAll('[data-calendar-summary-key]'));
                    const totalCountBadge = calendarCard.querySelector('[data-calendar-total-count]');
                    const detailDate = module.querySelector('[data-calendar-detail-date]');
                    const detailCount = module.querySelector('[data-calendar-detail-count]');
                    const detailBody = module.querySelector('[data-calendar-detail-body]');
                    const detailEmpty = module.querySelector('[data-calendar-detail-empty]');

                    const activeTypeFilters = new Set();
                    let activeStatusFilter = 'all';
                    let selectedDate = module.dataset.selectedDate || dayButtons[0]?.dataset.date || '';

                    const isDefaultFilterState = () => activeTypeFilters.size === 0 && activeStatusFilter === 'all';

                    const matchesTypeFilters = (item, typeFilters = activeTypeFilters) => typeFilters.size === 0
                        || typeFilters.has(resolveCalendarItemType(item));

                    const matchesStatusFilter = (item, statusFilter = activeStatusFilter) => statusFilter === 'all'
                        || resolveCalendarItemStatus(item) === statusFilter;

                    const matchesActiveFilters = (item) => matchesTypeFilters(item) && matchesStatusFilter(item);

                    const filteredCalendarItems = () => calendarItems.filter((item) => matchesActiveFilters(item));

                    const filteredItemsForDate = (date) => (dayLookup[date]?.items || []).filter((item) => matchesActiveFilters(item));

                    const countItems = (predicate) => calendarItems.filter((item) => predicate(item)).length;

                    const countForTypeFilter = (typeValue) => countItems((item) => {
                        return resolveCalendarItemType(item) === typeValue && matchesStatusFilter(item);
                    });

                    const countForStatusFilter = (statusValue) => countItems((item) => {
                        if (!matchesTypeFilters(item)) {
                            return false;
                        }

                        return statusValue === 'all'
                            ? true
                            : resolveCalendarItemStatus(item) === statusValue;
                    });

                    const syncFilterButtons = () => {
                        typeFilterButtons.forEach((button) => {
                            const value = button.dataset.calendarFilterValue || button.dataset.calendarFilter || '';
                            const isActive = activeTypeFilters.has(value);
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
                        const items = filteredCalendarItems();
                        const summaryCounts = {
                            events: items.filter((item) => resolveCalendarItemType(item) === 'event').length,
                            ongoing: items.filter((item) => resolveCalendarItemStatus(item) === 'ongoing').length,
                            planned: items.filter((item) => resolveCalendarItemStatus(item) === 'planned').length,
                            visits: items.filter((item) => resolveCalendarItemType(item) === 'visit').length,
                            birthdays: items.filter((item) => resolveCalendarItemType(item) === 'birthday').length,
                        };

                        if (totalCountBadge) {
                            totalCountBadge.textContent = replaceCount(module.dataset.countTemplate, items.length);
                        }

                        summaryCards.forEach((card) => {
                            const key = card.dataset.calendarSummaryKey || '';
                            const count = card.querySelector('[data-calendar-summary-count]');

                            if (count && key in summaryCounts) {
                                count.textContent = String(summaryCounts[key]);
                            }
                        });
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

                        return selectedDate || dayButtons[0]?.dataset.date || '';
                    };

                    const renderDayPreviews = () => {
                        dayButtons.forEach((button) => {
                            const date = button.dataset.date;
                            const items = filteredItemsForDate(date);
                            const previewableItems = items.filter((item) => !item.is_multi_day);
                            const preview = button.querySelector('[data-day-preview]');
                            const more = button.querySelector('[data-day-more]');
                            const count = button.querySelector('[data-day-count]');
                            const label = dayLookup[date]?.label || date;

                            preview.replaceChildren();
                            previewableItems.slice(0, 2).forEach((item) => preview.append(createPreviewItem(item)));

                            if (previewableItems.length > 2) {
                                more.hidden = false;
                                more.textContent = replaceCount(module.dataset.moreTemplate, previewableItems.length - 2);
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
                                const isVisible = matchesActiveFilters({
                                    type: span.dataset.type,
                                    status: span.dataset.status,
                                });

                                span.hidden = !isVisible;
                                hasVisibleItems ||= isVisible;
                            });

                            lane.hidden = !hasVisibleItems;
                        });
                    };

                    const renderDetail = () => {
                        selectedDate = resolveSelectedDate();

                        dayButtons.forEach((button) => {
                            const isSelected = button.dataset.date === selectedDate;
                            button.classList.toggle('is-selected', isSelected);
                            button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                        });

                        const selectedDay = dayLookup[selectedDate] || {label: selectedDate, items: []};
                        const items = filteredItemsForDate(selectedDate);

                        detailDate.textContent = selectedDay.label || selectedDate;
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

                    filterButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            const group = button.dataset.calendarFilterGroup || 'all';
                            const value = button.dataset.calendarFilterValue || button.dataset.calendarFilter || 'all';

                            if (group === 'type') {
                                if (activeTypeFilters.has(value) && activeTypeFilters.size === 1) {
                                    activeTypeFilters.clear();
                                } else {
                                    activeTypeFilters.clear();
                                    activeTypeFilters.add(value);
                                }
                            }

                            if (group === 'status') {
                                activeStatusFilter = value;
                            }

                            syncFilterButtons();
                            syncSummary();
                            renderDayPreviews();
                            renderSpanBars();
                            renderDetail();
                        });
                    });

                    dayButtons.forEach((button) => {
                        button.addEventListener('click', () => {
                            selectedDate = button.dataset.date || selectedDate;
                            renderDetail();
                        });
                    });

                    syncFilterButtons();
                    syncSummary();
                    renderDayPreviews();
                    renderSpanBars();
                    renderDetail();
                });
            });
        </script>
    @endpush
@endif

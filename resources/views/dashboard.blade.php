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
            <section class="content-card dashboard-calendar-card">
                <div class="section-heading dashboard-calendar-card__head">
                    <div class="dashboard-calendar-card__intro">
                        <p class="eyebrow">{{ __('ui.dashboard.calendar.eyebrow') }}</p>
                        <div class="dashboard-calendar-card__title-row">
                            <h2 class="section-title">{{ __('ui.dashboard.calendar.title') }}</h2>
                            <span class="badge">{{ __('ui.dashboard.calendar.event_count', ['count' => $eventCalendar['event_count']]) }}</span>
                        </div>
                        <p class="dashboard-calendar-card__subtitle">
                            {{ __('ui.dashboard.calendar.subtitle') }}
                        </p>
                    </div>

                    <div class="dashboard-calendar-card__controls">
                        <div class="dashboard-calendar-card__month-nav">
                            <a class="btn btn--ghost dashboard-calendar-card__nav" href="{{ $eventCalendar['prev_url'] }}" aria-label="{{ __('ui.dashboard.calendar.previous_month') }}">
                                <i class="material-icons" aria-hidden="true">chevron_left</i>
                            </a>

                            <div class="dashboard-calendar-card__month">
                                <strong>{{ $eventCalendar['month_label'] }}</strong>
                            </div>

                            <a class="btn btn--ghost dashboard-calendar-card__nav" href="{{ $eventCalendar['next_url'] }}" aria-label="{{ __('ui.dashboard.calendar.next_month') }}">
                                <i class="material-icons" aria-hidden="true">chevron_right</i>
                            </a>
                        </div>

                        @if ($eventCalendar['events_url'])
                            <a class="btn btn--ghost dashboard-calendar-card__link" href="{{ $eventCalendar['events_url'] }}">
                                <i class="material-icons" aria-hidden="true">calendar_month</i>
                                <span>{{ __('ui.dashboard.calendar.all_events') }}</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if (($eventCalendar['event_count'] ?? 0) > 0)
                    <div class="event-calendar" aria-label="{{ __('ui.dashboard.calendar.aria') }}">
                        <div class="event-calendar__weekdays">
                            @foreach ($eventCalendar['day_labels'] as $dayLabel)
                                <div class="event-calendar__weekday">{{ $dayLabel }}</div>
                            @endforeach
                        </div>

                        <div class="event-calendar__weeks">
                            @foreach ($eventCalendar['weeks'] as $week)
                                <section class="event-calendar__week">
                                    <div class="event-calendar__days">
                                        @foreach ($week['days'] as $day)
                                            <div class="event-calendar__day {{ $day['is_current_month'] ? '' : 'is-muted' }} {{ $day['is_today'] ? 'is-today' : '' }}">
                                                <time datetime="{{ $day['date'] }}">{{ $day['day_number'] }}</time>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="event-calendar__lanes">
                                        @foreach ($week['lanes'] as $lane)
                                            <div class="event-calendar__lane">
                                                @foreach ($lane as $segment)
                                                    <a
                                                        class="event-calendar__event event-calendar__event--{{ $segment['color'] }} {{ $segment['starts_before'] ? 'is-continued-left' : '' }} {{ $segment['ends_after'] ? 'is-continued-right' : '' }}"
                                                        href="{{ $segment['url'] }}"
                                                        style="grid-column: {{ $segment['start_column'] }} / span {{ $segment['span'] }}"
                                                        title="{{ $segment['tooltip'] }}"
                                                    >
                                                        <span class="event-calendar__event-title">{{ $segment['title'] }}</span>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="table-empty">
                        {{ __('ui.dashboard.calendar.empty') }}
                    </div>
                @endif
            </section>
        @endif

    </div>
@endsection

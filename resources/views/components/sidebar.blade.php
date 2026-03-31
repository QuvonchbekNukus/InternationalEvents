@php
    $dashboardActive = request()->routeIs('dashboard');
    $cooperationOpen = request()->routeIs('countries.*', 'organization-types.*', 'partner-organizations.*', 'partner-contacts.*');
    $eventsOpen = request()->routeIs('events.*', 'event-types.*');
    $agreementsOpen = request()->routeIs('agreements.*', 'agreement-types.*', 'agreement-directions.*');
    $visitsOpen = request()->routeIs('visits.*', 'visit-types.*');
    $settingsOpen = request()->routeIs('users.*', 'departments.*', 'ranks.*', 'document-types.*', 'activity-logs.*', 'role-permissions.*');
    $canManageRolePermissions = auth()->user()?->hasRole('super-admin') || auth()->user()?->can('manage role permissions');
    $sidebarIcons = [
        'mobile_toggle' => 'menu',
        'collapse_toggle' => 'chevron_left',
        'submenu_chevron' => 'chevron_right',
        'dashboard' => 'dashboard',
        'cooperation' => 'public',
        'agreements' => 'assignment',
        'events' => 'event',
        'visits' => 'flight_takeoff',
        'settings' => 'settings',
    ];
    $submenuIcons = [
        'countries' => 'public',
        'partner-organizations' => 'business',
        'partner-contacts' => 'perm_contact_calendar',
        'organization-types' => 'domain',
        'agreements' => 'description',
        'agreement-types' => 'category',
        'agreement-directions' => 'explore',
        'events-index' => 'event_note',
        'event-types' => 'category',
        'visits-index' => 'flight',
        'visit-types' => 'category',
        'document-types' => 'category',
        'users' => 'group',
        'departments' => 'apartment',
        'ranks' => 'military_tech',
        'activity-logs' => 'history',
        'role-permissions' => 'security',
    ];
    $renderSidebarIcon = function (string $icon, string $wrapperClass = 'ie-sidebar__item-icon') {
        return new \Illuminate\Support\HtmlString(
            '<span class="'.e($wrapperClass).'" aria-hidden="true"><i class="material-icons">'.e($icon).'</i></span>'
        );
    };
    $renderSubmenuContent = function (string $icon, string $label) {
        return new \Illuminate\Support\HtmlString(
            '<span class="ie-sidebar__submenu-icon" aria-hidden="true"><i class="material-icons">'.e($icon).'</i></span>'
            .'<span class="ie-sidebar__submenu-label">'.e($label).'</span>'
        );
    };
@endphp

<div class="ie-sidebar-shell" data-sidebar-shell>
    <button
        class="ie-sidebar-mobile-toggle"
        type="button"
        data-sidebar-mobile-toggle
        aria-label="{{ __('ui.sidebar.open') }}"
        aria-expanded="false"
    >
        <i class="material-icons" aria-hidden="true">{{ $sidebarIcons['mobile_toggle'] }}</i>
    </button>

    <button class="ie-sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="{{ __('ui.sidebar.close') }}"></button>

    <aside class="ie-sidebar" data-sidebar aria-label="{{ __('ui.sidebar.navigation') }}">
        <div class="ie-sidebar__surface">
            <header class="ie-sidebar__profile">
                <div class="ie-sidebar__profile-body">
                    <div class="ie-sidebar__avatar">
                        <img
                            class="ie-sidebar__brand-logo"
                            src="{{ asset('logo/mgv.png') }}"
                            alt="{{ __('ui.sidebar.brand_alt') }}"
                        >
                    </div>

                    <div class="ie-sidebar__identity">
                        <h2 class="ie-sidebar__name ie-sidebar__name--brand">{{ __('ui.nav.brand_title') }}</h2>
                    </div>
                </div>

                <button
                    class="ie-sidebar__collapse-toggle"
                    type="button"
                    data-sidebar-toggle
                    data-expand-label="{{ __('ui.sidebar.expand') }}"
                    data-collapse-label="{{ __('ui.sidebar.collapse') }}"
                    aria-label="{{ __('ui.sidebar.collapse') }}"
                    aria-expanded="true"
                >
                    <i class="material-icons" aria-hidden="true">{{ $sidebarIcons['collapse_toggle'] }}</i>
                </button>
            </header>

            <section class="ie-sidebar__section">
                <p class="ie-sidebar__section-title">{{ __('ui.sidebar.sections.main') }}</p>

                <nav class="ie-sidebar__nav" aria-label="{{ __('ui.sidebar.menu') }}">
                    <a class="ie-sidebar__item {{ $dashboardActive ? 'is-active' : '' }}" href="{{ route('dashboard') }}" data-sidebar-item="dashboard">
                        {!! $renderSidebarIcon($sidebarIcons['dashboard']) !!}
                        <span class="ie-sidebar__item-copy">
                            <span class="ie-sidebar__item-title">{{ __('ui.sidebar.dashboard') }}</span>
                        </span>
                    </a>

                    @canany(['view countries', 'view organization types', 'view partner organizations', 'view partner contacts'])
                        <div class="ie-sidebar__nav-group" data-submenu-group="cooperation">
                            <button
                                class="ie-sidebar__item {{ $cooperationOpen ? 'is-active' : '' }}"
                                type="button"
                                data-sidebar-item="cooperation"
                                data-submenu-trigger
                                aria-expanded="{{ $cooperationOpen ? 'true' : 'false' }}"
                                aria-haspopup="true"
                                aria-controls="ie-sidebar-inline-submenu-cooperation"
                            >
                                {!! $renderSidebarIcon($sidebarIcons['cooperation']) !!}
                                <span class="ie-sidebar__item-copy">
                                    <span class="ie-sidebar__item-title">{{ __('ui.sidebar.cooperation') }}</span>
                                </span>
                                {!! $renderSidebarIcon($sidebarIcons['submenu_chevron'], 'ie-sidebar__item-chevron') !!}
                            </button>

                            <div class="ie-sidebar__submenu {{ $cooperationOpen ? 'is-open' : '' }}" id="ie-sidebar-inline-submenu-cooperation" data-inline-submenu="cooperation">
                                @can('view countries')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('countries.*') ? 'is-active' : '' }}" href="{{ route('countries.index') }}" data-parent-group="cooperation" data-submenu-item="countries">
                                        {!! $renderSubmenuContent($submenuIcons['countries'], __('ui.sidebar.countries')) !!}
                                    </a>
                                @endcan

                                @can('view partner organizations')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('partner-organizations.*') ? 'is-active' : '' }}" href="{{ route('partner-organizations.index') }}" data-parent-group="cooperation" data-submenu-item="partner-organizations">
                                        {!! $renderSubmenuContent($submenuIcons['partner-organizations'], __('ui.sidebar.partner_organizations')) !!}
                                    </a>
                                @endcan

                                @can('view partner contacts')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('partner-contacts.*') ? 'is-active' : '' }}" href="{{ route('partner-contacts.index') }}" data-parent-group="cooperation" data-submenu-item="partner-contacts">
                                        {!! $renderSubmenuContent($submenuIcons['partner-contacts'], __('ui.sidebar.partner_contacts')) !!}
                                    </a>
                                @endcan

                                @can('view organization types')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('organization-types.*') ? 'is-active' : '' }}" href="{{ route('organization-types.index') }}" data-parent-group="cooperation" data-submenu-item="organization-types">
                                        {!! $renderSubmenuContent($submenuIcons['organization-types'], __('ui.sidebar.organization_types')) !!}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcanany

                    @canany(['view agreements', 'view own agreements', 'view agreement types', 'view agreement directions'])
                        <div class="ie-sidebar__nav-group" data-submenu-group="agreements">
                            <button
                                class="ie-sidebar__item {{ $agreementsOpen ? 'is-active' : '' }}"
                                type="button"
                                data-sidebar-item="agreements"
                                data-submenu-trigger
                                aria-expanded="{{ $agreementsOpen ? 'true' : 'false' }}"
                                aria-haspopup="true"
                                aria-controls="ie-sidebar-inline-submenu-agreements"
                            >
                                {!! $renderSidebarIcon($sidebarIcons['agreements']) !!}
                                <span class="ie-sidebar__item-copy">
                                    <span class="ie-sidebar__item-title">{{ __('ui.sidebar.agreements') }}</span>
                                </span>
                                {!! $renderSidebarIcon($sidebarIcons['submenu_chevron'], 'ie-sidebar__item-chevron') !!}
                            </button>

                            <div class="ie-sidebar__submenu {{ $agreementsOpen ? 'is-open' : '' }}" id="ie-sidebar-inline-submenu-agreements" data-inline-submenu="agreements">
                                @canany(['view agreements', 'view own agreements'])
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('agreements.*') ? 'is-active' : '' }}" href="{{ route('agreements.index') }}" data-parent-group="agreements" data-submenu-item="agreements">
                                        {!! $renderSubmenuContent($submenuIcons['agreements'], __('ui.sidebar.all_agreements')) !!}
                                    </a>
                                @endcanany

                                @can('view agreement types')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('agreement-types.*') ? 'is-active' : '' }}" href="{{ route('agreement-types.index') }}" data-parent-group="agreements" data-submenu-item="agreement-types">
                                        {!! $renderSubmenuContent($submenuIcons['agreement-types'], __('ui.sidebar.agreement_types')) !!}
                                    </a>
                                @endcan

                                @can('view agreement directions')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('agreement-directions.*') ? 'is-active' : '' }}" href="{{ route('agreement-directions.index') }}" data-parent-group="agreements" data-submenu-item="agreement-directions">
                                        {!! $renderSubmenuContent($submenuIcons['agreement-directions'], __('ui.sidebar.agreement_directions')) !!}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcanany

                    @canany(['view events', 'view own events', 'view event types'])
                        <div class="ie-sidebar__nav-group" data-submenu-group="events">
                            <button
                                class="ie-sidebar__item {{ $eventsOpen ? 'is-active' : '' }}"
                                type="button"
                                data-sidebar-item="events"
                                data-submenu-trigger
                                aria-expanded="{{ $eventsOpen ? 'true' : 'false' }}"
                                aria-haspopup="true"
                                aria-controls="ie-sidebar-inline-submenu-events"
                            >
                                {!! $renderSidebarIcon($sidebarIcons['events']) !!}
                                <span class="ie-sidebar__item-copy">
                                    <span class="ie-sidebar__item-title">{{ __('ui.sidebar.events') }}</span>
                                </span>
                                {!! $renderSidebarIcon($sidebarIcons['submenu_chevron'], 'ie-sidebar__item-chevron') !!}
                            </button>

                            <div class="ie-sidebar__submenu {{ $eventsOpen ? 'is-open' : '' }}" id="ie-sidebar-inline-submenu-events" data-inline-submenu="events">
                                @canany(['view events', 'view own events'])
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('events.*') ? 'is-active' : '' }}" href="{{ route('events.index') }}" data-parent-group="events" data-submenu-item="events-index">
                                        {!! $renderSubmenuContent($submenuIcons['events-index'], __('ui.sidebar.all_events')) !!}
                                    </a>
                                @endcanany

                                @can('view event types')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('event-types.*') ? 'is-active' : '' }}" href="{{ route('event-types.index') }}" data-parent-group="events" data-submenu-item="event-types">
                                        {!! $renderSubmenuContent($submenuIcons['event-types'], __('ui.sidebar.event_types')) !!}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcanany

                    @canany(['view visits', 'view own visits', 'view visit types'])
                        <div class="ie-sidebar__nav-group" data-submenu-group="visits">
                            <button
                                class="ie-sidebar__item {{ $visitsOpen ? 'is-active' : '' }}"
                                type="button"
                                data-sidebar-item="visits"
                                data-submenu-trigger
                                aria-expanded="{{ $visitsOpen ? 'true' : 'false' }}"
                                aria-haspopup="true"
                                aria-controls="ie-sidebar-inline-submenu-visits"
                            >
                                {!! $renderSidebarIcon($sidebarIcons['visits']) !!}
                                <span class="ie-sidebar__item-copy">
                                    <span class="ie-sidebar__item-title">{{ __('ui.sidebar.visits') }}</span>
                                </span>
                                {!! $renderSidebarIcon($sidebarIcons['submenu_chevron'], 'ie-sidebar__item-chevron') !!}
                            </button>

                            <div class="ie-sidebar__submenu {{ $visitsOpen ? 'is-open' : '' }}" id="ie-sidebar-inline-submenu-visits" data-inline-submenu="visits">
                                @canany(['view visits', 'view own visits'])
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('visits.*') ? 'is-active' : '' }}" href="{{ route('visits.index') }}" data-parent-group="visits" data-submenu-item="visits-index">
                                        {!! $renderSubmenuContent($submenuIcons['visits-index'], __('ui.sidebar.all_visits')) !!}
                                    </a>
                                @endcanany

                                @can('view visit types')
                                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('visit-types.*') ? 'is-active' : '' }}" href="{{ route('visit-types.index') }}" data-parent-group="visits" data-submenu-item="visit-types">
                                        {!! $renderSubmenuContent($submenuIcons['visit-types'], __('ui.sidebar.visit_types')) !!}
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcanany

                    <div class="ie-sidebar__nav-group" data-submenu-group="settings">
                        <button
                            class="ie-sidebar__item {{ $settingsOpen ? 'is-active' : '' }}"
                            type="button"
                            data-sidebar-item="settings"
                            data-submenu-trigger
                            aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}"
                            aria-haspopup="true"
                            aria-controls="ie-sidebar-inline-submenu-settings"
                        >
                            {!! $renderSidebarIcon($sidebarIcons['settings']) !!}
                            <span class="ie-sidebar__item-copy">
                                <span class="ie-sidebar__item-title">{{ __('ui.sidebar.settings') }}</span>
                            </span>
                            {!! $renderSidebarIcon($sidebarIcons['submenu_chevron'], 'ie-sidebar__item-chevron') !!}
                        </button>

                        <div class="ie-sidebar__submenu {{ $settingsOpen ? 'is-open' : '' }}" id="ie-sidebar-inline-submenu-settings" data-inline-submenu="settings">
                            @canany(['view users', 'view own users'])
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}" href="{{ route('users.index') }}" data-parent-group="settings" data-submenu-item="users">
                                    {!! $renderSubmenuContent($submenuIcons['users'], __('ui.sidebar.users')) !!}
                                </a>
                            @endcanany

                            @can('view departments')
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('departments.*') ? 'is-active' : '' }}" href="{{ route('departments.index') }}" data-parent-group="settings" data-submenu-item="departments">
                                    {!! $renderSubmenuContent($submenuIcons['departments'], __('ui.sidebar.departments')) !!}
                                </a>
                            @endcan

                            @can('view ranks')
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('ranks.*') ? 'is-active' : '' }}" href="{{ route('ranks.index') }}" data-parent-group="settings" data-submenu-item="ranks">
                                    {!! $renderSubmenuContent($submenuIcons['ranks'], __('ui.sidebar.ranks')) !!}
                                </a>
                            @endcan

                            @can('view document types')
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('document-types.*') ? 'is-active' : '' }}" href="{{ route('document-types.index') }}" data-parent-group="settings" data-submenu-item="document-types">
                                    {!! $renderSubmenuContent($submenuIcons['document-types'], __('ui.sidebar.document_types')) !!}
                                </a>
                            @endcan

                            @can('view activity logs')
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('activity-logs.*') ? 'is-active' : '' }}" href="{{ route('activity-logs.index') }}" data-parent-group="settings" data-submenu-item="activity-logs">
                                    {!! $renderSubmenuContent($submenuIcons['activity-logs'], __('ui.sidebar.activity_logs')) !!}
                                </a>
                            @endcan

                            @if ($canManageRolePermissions)
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('role-permissions.*') ? 'is-active' : '' }}" href="{{ route('role-permissions.index') }}" data-parent-group="settings" data-submenu-item="role-permissions">
                                    {!! $renderSubmenuContent($submenuIcons['role-permissions'], __('ui.sidebar.role_permissions')) !!}
                                </a>
                            @endif
                        </div>
                    </div>
                </nav>
            </section>

            <div class="ie-sidebar__spacer"></div>
        </div>
    </aside>

    @canany(['view countries', 'view organization types', 'view partner organizations', 'view partner contacts'])
        <div class="ie-sidebar__floating-panel" data-floating-panel="cooperation" hidden>
            <p class="ie-sidebar__floating-title">{{ __('ui.sidebar.cooperation') }}</p>
            <div class="ie-sidebar__floating-list">
                @can('view countries')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('countries.*') ? 'is-active' : '' }}" href="{{ route('countries.index') }}" data-parent-group="cooperation" data-submenu-item="countries">
                        {!! $renderSubmenuContent($submenuIcons['countries'], __('ui.sidebar.countries')) !!}
                    </a>
                @endcan

                @can('view partner organizations')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('partner-organizations.*') ? 'is-active' : '' }}" href="{{ route('partner-organizations.index') }}" data-parent-group="cooperation" data-submenu-item="partner-organizations">
                        {!! $renderSubmenuContent($submenuIcons['partner-organizations'], __('ui.sidebar.partner_organizations')) !!}
                    </a>
                @endcan

                @can('view partner contacts')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('partner-contacts.*') ? 'is-active' : '' }}" href="{{ route('partner-contacts.index') }}" data-parent-group="cooperation" data-submenu-item="partner-contacts">
                        {!! $renderSubmenuContent($submenuIcons['partner-contacts'], __('ui.sidebar.partner_contacts')) !!}
                    </a>
                @endcan

                @can('view organization types')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('organization-types.*') ? 'is-active' : '' }}" href="{{ route('organization-types.index') }}" data-parent-group="cooperation" data-submenu-item="organization-types">
                        {!! $renderSubmenuContent($submenuIcons['organization-types'], __('ui.sidebar.organization_types')) !!}
                    </a>
                @endcan
            </div>
        </div>
    @endcanany

    @canany(['view events', 'view own events', 'view event types'])
        <div class="ie-sidebar__floating-panel" data-floating-panel="events" hidden>
            <p class="ie-sidebar__floating-title">{{ __('ui.sidebar.events') }}</p>
            <div class="ie-sidebar__floating-list">
                @canany(['view events', 'view own events'])
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('events.*') ? 'is-active' : '' }}" href="{{ route('events.index') }}" data-parent-group="events" data-submenu-item="events-index">
                        {!! $renderSubmenuContent($submenuIcons['events-index'], __('ui.sidebar.all_events')) !!}
                    </a>
                @endcanany

                @can('view event types')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('event-types.*') ? 'is-active' : '' }}" href="{{ route('event-types.index') }}" data-parent-group="events" data-submenu-item="event-types">
                        {!! $renderSubmenuContent($submenuIcons['event-types'], __('ui.sidebar.event_types')) !!}
                    </a>
                @endcan
            </div>
        </div>
    @endcanany

    @canany(['view agreements', 'view own agreements', 'view agreement types', 'view agreement directions'])
        <div class="ie-sidebar__floating-panel" data-floating-panel="agreements" hidden>
            <p class="ie-sidebar__floating-title">{{ __('ui.sidebar.agreements') }}</p>
            <div class="ie-sidebar__floating-list">
                @canany(['view agreements', 'view own agreements'])
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('agreements.*') ? 'is-active' : '' }}" href="{{ route('agreements.index') }}" data-parent-group="agreements" data-submenu-item="agreements">
                        {!! $renderSubmenuContent($submenuIcons['agreements'], __('ui.sidebar.all_agreements')) !!}
                    </a>
                @endcanany

                @can('view agreement types')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('agreement-types.*') ? 'is-active' : '' }}" href="{{ route('agreement-types.index') }}" data-parent-group="agreements" data-submenu-item="agreement-types">
                        {!! $renderSubmenuContent($submenuIcons['agreement-types'], __('ui.sidebar.agreement_types')) !!}
                    </a>
                @endcan

                @can('view agreement directions')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('agreement-directions.*') ? 'is-active' : '' }}" href="{{ route('agreement-directions.index') }}" data-parent-group="agreements" data-submenu-item="agreement-directions">
                        {!! $renderSubmenuContent($submenuIcons['agreement-directions'], __('ui.sidebar.agreement_directions')) !!}
                    </a>
                @endcan
            </div>
        </div>
    @endcanany

    @canany(['view visits', 'view own visits', 'view visit types'])
        <div class="ie-sidebar__floating-panel" data-floating-panel="visits" hidden>
            <p class="ie-sidebar__floating-title">{{ __('ui.sidebar.visits') }}</p>
            <div class="ie-sidebar__floating-list">
                @canany(['view visits', 'view own visits'])
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('visits.*') ? 'is-active' : '' }}" href="{{ route('visits.index') }}" data-parent-group="visits" data-submenu-item="visits-index">
                        {!! $renderSubmenuContent($submenuIcons['visits-index'], __('ui.sidebar.all_visits')) !!}
                    </a>
                @endcanany

                @can('view visit types')
                    <a class="ie-sidebar__submenu-item {{ request()->routeIs('visit-types.*') ? 'is-active' : '' }}" href="{{ route('visit-types.index') }}" data-parent-group="visits" data-submenu-item="visit-types">
                        {!! $renderSubmenuContent($submenuIcons['visit-types'], __('ui.sidebar.visit_types')) !!}
                    </a>
                @endcan
            </div>
        </div>
    @endcanany

    <div class="ie-sidebar__floating-panel" data-floating-panel="settings" hidden>
        <p class="ie-sidebar__floating-title">{{ __('ui.sidebar.settings') }}</p>
        <div class="ie-sidebar__floating-list">
            @canany(['view users', 'view own users'])
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}" href="{{ route('users.index') }}" data-parent-group="settings" data-submenu-item="users">
                    {!! $renderSubmenuContent($submenuIcons['users'], __('ui.sidebar.users')) !!}
                </a>
            @endcanany

            @can('view departments')
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('departments.*') ? 'is-active' : '' }}" href="{{ route('departments.index') }}" data-parent-group="settings" data-submenu-item="departments">
                    {!! $renderSubmenuContent($submenuIcons['departments'], __('ui.sidebar.departments')) !!}
                </a>
            @endcan

            @can('view ranks')
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('ranks.*') ? 'is-active' : '' }}" href="{{ route('ranks.index') }}" data-parent-group="settings" data-submenu-item="ranks">
                    {!! $renderSubmenuContent($submenuIcons['ranks'], __('ui.sidebar.ranks')) !!}
                </a>
            @endcan

            @can('view document types')
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('document-types.*') ? 'is-active' : '' }}" href="{{ route('document-types.index') }}" data-parent-group="settings" data-submenu-item="document-types">
                    {!! $renderSubmenuContent($submenuIcons['document-types'], __('ui.sidebar.document_types')) !!}
                </a>
            @endcan

            @can('view activity logs')
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('activity-logs.*') ? 'is-active' : '' }}" href="{{ route('activity-logs.index') }}" data-parent-group="settings" data-submenu-item="activity-logs">
                    {!! $renderSubmenuContent($submenuIcons['activity-logs'], __('ui.sidebar.activity_logs')) !!}
                </a>
            @endcan

            @if ($canManageRolePermissions)
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('role-permissions.*') ? 'is-active' : '' }}" href="{{ route('role-permissions.index') }}" data-parent-group="settings" data-submenu-item="role-permissions">
                    {!! $renderSubmenuContent($submenuIcons['role-permissions'], __('ui.sidebar.role_permissions')) !!}
                </a>
            @endif
        </div>
    </div>
</div>

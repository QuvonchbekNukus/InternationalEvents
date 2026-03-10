@php
    $managementOpen = request()->routeIs('dashboard', 'users.*', 'departments.*', 'ranks.*');
    $settingsActive = request()->routeIs('profile.edit');
@endphp

<div class="ie-sidebar-shell" data-sidebar-shell>
    <button
        class="ie-sidebar-mobile-toggle"
        type="button"
        data-sidebar-mobile-toggle
        aria-label="Yon panelni ochish"
        aria-expanded="false"
    >
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="4" y="4" width="6" height="6" rx="2" stroke="currentColor" stroke-width="1.6"/>
            <rect x="14" y="4" width="6" height="6" rx="2" stroke="currentColor" stroke-width="1.6"/>
            <rect x="4" y="14" width="6" height="6" rx="2" stroke="currentColor" stroke-width="1.6"/>
            <path d="M15 15h5M15 19h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    </button>

    <button class="ie-sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="Yon panelni yopish"></button>

    <aside class="ie-sidebar" data-sidebar aria-label="Asosiy navigatsiya">
        <div class="ie-sidebar__surface">
            <header class="ie-sidebar__profile">
                <div class="ie-sidebar__profile-body">
                    <div class="ie-sidebar__avatar">
                        <img
                            class="ie-sidebar__brand-logo"
                            src="{{ asset('logo/mgv.png') }}"
                            alt="Milliy Gvardiya logotipi"
                        >
                    </div>

                    <div class="ie-sidebar__identity">
                        <h2 class="ie-sidebar__name ie-sidebar__name--brand">MILLIY GVARDIYA</h2>
                    </div>
                </div>

                <button
                    class="ie-sidebar__collapse-toggle"
                    type="button"
                    data-sidebar-toggle
                    aria-label="Yon panelni yig'ish"
                    aria-expanded="true"
                >
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 4.5v15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        <path d="M16 7.5L11 12l5 4.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </header>

            <section class="ie-sidebar__section">
                <p class="ie-sidebar__section-title">MAIN</p>

                <nav class="ie-sidebar__nav" aria-label="Asosiy menyu">
                    <div class="ie-sidebar__nav-group">
                        <button
                            class="ie-sidebar__item {{ $managementOpen ? 'is-active' : '' }}"
                            type="button"
                            data-sidebar-item="asosiy"
                            data-submenu-trigger
                            aria-expanded="{{ $managementOpen ? 'true' : 'false' }}"
                            aria-haspopup="true"
                            aria-controls="ie-sidebar-inline-submenu"
                        >
                            <span class="ie-sidebar__item-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M3.5 10.5L12 4l8.5 6.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M6.5 9.5v10h11v-10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M10 19.5v-4.8h4v4.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="ie-sidebar__item-copy">
                                <span class="ie-sidebar__item-title">Asosiy</span>
                            </span>
                            <span class="ie-sidebar__item-chevron" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </button>

                        <div class="ie-sidebar__submenu {{ $managementOpen ? 'is-open' : '' }}" id="ie-sidebar-inline-submenu" data-inline-submenu>
                            <a class="ie-sidebar__submenu-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}" data-submenu-item="dashboard">
                                Dashboard
                            </a>

                            @can('view users')
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}" href="{{ route('users.index') }}" data-submenu-item="users">
                                    Foydalanuvchilar
                                </a>
                            @endcan

                            @can('view departments')
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('departments.*') ? 'is-active' : '' }}" href="{{ route('departments.index') }}" data-submenu-item="departments">
                                    Bo'limlar
                                </a>
                            @endcan

                            @can('view ranks')
                                <a class="ie-sidebar__submenu-item {{ request()->routeIs('ranks.*') ? 'is-active' : '' }}" href="{{ route('ranks.index') }}" data-submenu-item="ranks">
                                    Unvonlar
                                </a>
                            @endcan
                        </div>
                    </div>

                    <button class="ie-sidebar__item" type="button" data-sidebar-item="tadbirlar">
                        <span class="ie-sidebar__item-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <rect x="4" y="5" width="16" height="15" rx="3.5" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M8 3.5v3.2M16 3.5v3.2M4 10h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                <path d="M12.5 13l.8 1.7 1.9.3-1.4 1.3.3 1.9-1.6-.9-1.6.9.3-1.9-1.4-1.3 1.9-.3.8-1.7Z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="ie-sidebar__item-copy">
                            <span class="ie-sidebar__item-title">Tadbirlar</span>
                        </span>
                    </button>

                    <button class="ie-sidebar__item" type="button" data-sidebar-item="tashriflar">
                        <span class="ie-sidebar__item-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <circle cx="7.5" cy="16.5" r="2.5" stroke="currentColor" stroke-width="1.7"/>
                                <circle cx="16.5" cy="7.5" r="2.5" stroke="currentColor" stroke-width="1.7"/>
                                <path d="M9.7 15.4c2-.8 3.2-1.8 4.2-3.2 1-1.4 1.2-2.3 1.4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="ie-sidebar__item-copy">
                            <span class="ie-sidebar__item-title">Tashriflar</span>
                        </span>
                    </button>

                    <button class="ie-sidebar__item" type="button" data-sidebar-item="hujjatlar">
                        <span class="ie-sidebar__item-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M8 4.5h7l3 3V18a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V6.5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                <path d="M15 4.5v3.2h3.2M10 12.5h4M10 16h6M6 8H4.8A1.8 1.8 0 0 0 3 9.8v8.4A1.8 1.8 0 0 0 4.8 20H6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="ie-sidebar__item-copy">
                            <span class="ie-sidebar__item-title">Hujjatlar</span>
                        </span>
                    </button>

                    <a class="ie-sidebar__item {{ $settingsActive ? 'is-active' : '' }}" href="{{ route('profile.edit') }}" data-sidebar-item="sozlamalar">
                        <span class="ie-sidebar__item-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path d="M4 7h6M14 7h6M4 12h10M18 12h2M4 17h3M11 17h9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                <circle cx="11" cy="7" r="2" stroke="currentColor" stroke-width="1.7"/>
                                <circle cx="16" cy="12" r="2" stroke="currentColor" stroke-width="1.7"/>
                                <circle cx="9" cy="17" r="2" stroke="currentColor" stroke-width="1.7"/>
                            </svg>
                        </span>
                        <span class="ie-sidebar__item-copy">
                            <span class="ie-sidebar__item-title">Sozlamalar</span>
                        </span>
                    </a>
                </nav>
            </section>

            <div class="ie-sidebar__spacer"></div>
        </div>
    </aside>

    <div class="ie-sidebar__floating-panel" data-floating-panel hidden>
        <p class="ie-sidebar__floating-title">Asosiy</p>
        <div class="ie-sidebar__floating-list">
            <a class="ie-sidebar__submenu-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}" data-submenu-item="dashboard">
                Dashboard
            </a>

            @can('view users')
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}" href="{{ route('users.index') }}" data-submenu-item="users">
                    Foydalanuvchilar
                </a>
            @endcan

            @can('view departments')
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('departments.*') ? 'is-active' : '' }}" href="{{ route('departments.index') }}" data-submenu-item="departments">
                    Bo'limlar
                </a>
            @endcan

            @can('view ranks')
                <a class="ie-sidebar__submenu-item {{ request()->routeIs('ranks.*') ? 'is-active' : '' }}" href="{{ route('ranks.index') }}" data-submenu-item="ranks">
                    Unvonlar
                </a>
            @endcan
        </div>
    </div>
</div>

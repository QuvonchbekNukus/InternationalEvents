@php
    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first();
    $translatedRole = $roleName ? __("ui.roles.$roleName") : __('ui.roles.user');
    $roleLabel = $roleName && $translatedRole === "ui.roles.$roleName"
        ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $roleName))
        : $translatedRole;
    $unreadNotificationsCount = $navbarUnreadNotificationsCount ?? 0;
    $navbarRecentNotifications = $navbarRecentNotifications ?? collect();
    $availableLocales = config('app.supported_locales', []);
    $currentLocale = app()->getLocale();

    if (! isset($availableLocales[$currentLocale]) && $availableLocales !== []) {
        $currentLocale = (string) array_key_first($availableLocales);
    }

    $currentLocaleMeta = $availableLocales[$currentLocale] ?? [
        'short' => strtoupper($currentLocale),
    ];
@endphp

<nav class="topbar" data-topbar aria-label="{{ __('ui.nav.top_navigation') }}">
    <div class="topbar-left">
        <div class="topbar-brand">
            <span class="topbar-title">{{ __('ui.nav.brand_title') }}</span>
            <span class="topbar-subtitle">{{ __('ui.nav.brand_subtitle') }}</span>
        </div>
    </div>

    <div class="topbar-center">
        <label class="topbar-search" aria-label="{{ __('ui.nav.search_label') }}">
            <i class="material-icons app-icon app-icon--md topbar-icon" aria-hidden="true">search</i>
            <input type="text" placeholder="{{ __('ui.nav.search_placeholder') }}" />
        </label>
    </div>

    <div class="topbar-right">
        <div class="topbar-actions">
            <form class="topbar-locale-form" method="POST" action="{{ route('locale.switch') }}">
                @csrf

                <div class="topbar-locale">
                    <span class="topbar-locale-mark" aria-hidden="true">
                        <i class="material-icons app-icon app-icon--md topbar-icon">translate</i>
                    </span>

                    <span class="topbar-locale-copy">
                        <span class="topbar-locale-label">{{ __('ui.nav.language') }}</span>
                        <span class="topbar-locale-value">
                            {{ $currentLocaleMeta['label'] ?? strtoupper($currentLocale) }}
                        </span>
                    </span>

                    <span class="topbar-locale-current" aria-hidden="true">
                        {{ $currentLocaleMeta['short'] ?? strtoupper($currentLocale) }}
                    </span>

                    <span class="topbar-locale-arrow" aria-hidden="true">
                        <i class="material-icons app-icon app-icon--md topbar-icon">expand_more</i>
                    </span>

                    <select
                        id="topbar-locale-select"
                        class="topbar-locale-select"
                        name="locale"
                        aria-label="{{ __('ui.nav.language') }}"
                        onchange="this.form.submit()"
                    >
                        @foreach ($availableLocales as $localeCode => $localeMeta)
                            <option value="{{ $localeCode }}" @selected($localeCode === $currentLocale)>
                                {{ $localeMeta['label'] ?? strtoupper($localeCode) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="topbar-notification-dropdown" data-notification-dropdown>
                <button
                    type="button"
                    class="topbar-icon-button topbar-notification"
                    data-notification-dropdown-trigger
                    aria-label="{{ __('ui.notifications_dropdown.open_notifications') }}"
                    aria-expanded="false"
                    aria-haspopup="dialog"
                    aria-controls="topbar-notification-panel"
                >
                    <i class="material-icons app-icon app-icon--md topbar-icon" aria-hidden="true">notifications</i>
                    @if ($unreadNotificationsCount > 0)
                        <span class="topbar-badge">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                    @endif
                </button>

                <div
                    id="topbar-notification-panel"
                    class="topbar-notification-panel"
                    data-notification-dropdown-panel
                    role="dialog"
                    aria-label="{{ __('ui.notifications_dropdown.panel_aria') }}"
                    aria-modal="false"
                    hidden
                >
                    <div class="topbar-notification-panel__head">
                        <span class="topbar-notification-panel__title">{{ __('ui.notifications_dropdown.panel_title') }}</span>
                    </div>

                    <div class="topbar-notification-panel__body">
                        @forelse ($navbarRecentNotifications as $notification)
                            @php
                                $targetUrl = $notification->resolveTargetUrl();
                            @endphp
                            <div
                                class="topbar-notification-row topbar-notification-item--{{ $notification->related_kind_slug }} {{ $notification->is_read ? '' : 'is-unread' }}"
                            >
                                @if ($targetUrl)
                                    <a
                                        class="topbar-notification-item__main"
                                        href="{{ route('notifications.go', $notification) }}"
                                    >
                                        <span class="topbar-notification-item__icon" aria-hidden="true">
                                            <i class="material-icons">{{ $notification->related_category_icon }}</i>
                                        </span>
                                        <span class="topbar-notification-item__text">
                                            <span class="topbar-notification-item__kind">{{ $notification->related_kind_label }}</span>
                                            <span class="topbar-notification-item__title">{{ $notification->display_title }}</span>
                                            <span class="topbar-notification-item__preview">{{ $notification->preview_text }}</span>
                                            <span class="topbar-notification-item__meta">{{ $notification->created_at?->diffForHumans() }}</span>
                                        </span>
                                        @if (! $notification->is_read)
                                            <span class="topbar-notification-item__dot" aria-hidden="true"></span>
                                        @endif
                                    </a>
                                @else
                                    <div class="topbar-notification-item__main topbar-notification-item__main--static is-disabled">
                                        <span class="topbar-notification-item__icon" aria-hidden="true">
                                            <i class="material-icons">{{ $notification->related_category_icon }}</i>
                                        </span>
                                        <span class="topbar-notification-item__text">
                                            <span class="topbar-notification-item__kind">{{ $notification->related_kind_label }}</span>
                                            <span class="topbar-notification-item__title">{{ $notification->display_title }}</span>
                                            <span class="topbar-notification-item__preview">{{ $notification->preview_text }}</span>
                                            <span class="topbar-notification-item__meta">{{ $notification->created_at?->diffForHumans() }}</span>
                                        </span>
                                    </div>
                                @endif
                                @if (! $notification->is_read)
                                    <form
                                        class="topbar-notification-item__read-form"
                                        method="post"
                                        action="{{ route('notifications.read', $notification) }}"
                                    >
                                        @csrf
                                        <button class="topbar-notification-item__read-btn" type="submit">
                                            {{ __('ui.notifications_dropdown.mark_read') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="topbar-notification-panel__empty">{{ __('ui.notifications_dropdown.empty') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <a class="profile-chip" href="{{ route('profile.edit') }}" aria-label="{{ __('ui.nav.profile') }}">
                <span class="profile-avatar" aria-hidden="true">
                    <i class="material-icons app-icon app-icon--md topbar-icon">account_circle</i>
                </span>

                <span class="profile-copy">
                    <span class="profile-copy-label">{{ $roleLabel }}</span>
                    <span class="profile-copy-name">{{ $currentUser?->full_name ?? __('ui.roles.user') }}</span>
                </span>
            </a>

            <form class="topbar-logout-form" method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="topbar-logout" type="submit" aria-label="{{ __('ui.nav.logout') }}">
                    <span class="topbar-logout-mark" aria-hidden="true">
                        <i class="material-icons app-icon app-icon--md topbar-icon">exit_to_app</i>
                    </span>
                    <span>{{ __('ui.nav.logout') }}</span>
                </button>
            </form>
        </div>
    </div>
</nav>

@php
    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first();
    $translatedRole = $roleName ? __("ui.roles.$roleName") : __('ui.roles.user');
    $roleLabel = $roleName && $translatedRole === "ui.roles.$roleName"
        ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $roleName))
        : $translatedRole;
    $unreadNotificationsCount = $navbarUnreadNotificationsCount ?? 0;
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
            <i class="material-icons topbar-icon" aria-hidden="true">search</i>
            <input type="text" placeholder="{{ __('ui.nav.search_placeholder') }}" />
        </label>
    </div>

    <div class="topbar-right">
        <div class="topbar-actions">
            <form class="topbar-locale-form" method="POST" action="{{ route('locale.switch') }}">
                @csrf

                <div class="topbar-locale">
                    <span class="topbar-locale-mark" aria-hidden="true">
                        <i class="material-icons topbar-icon">translate</i>
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
                        <i class="material-icons topbar-icon">expand_more</i>
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

            <a
                class="topbar-icon-button topbar-notification"
                href="{{ route('profile.edit') }}#profile-notifications"
                data-topbar-notification
                aria-label="{{ __('ui.nav.notifications') }}"
            >
                <i class="material-icons topbar-icon" aria-hidden="true">notifications</i>
                @if ($unreadNotificationsCount > 0)
                    <span class="topbar-badge">{{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}</span>
                @endif
            </a>

            <a class="profile-chip" href="{{ route('profile.edit') }}" aria-label="{{ __('ui.nav.profile') }}">
                <span class="profile-avatar" aria-hidden="true">
                    <i class="material-icons topbar-icon">account_circle</i>
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
                        <i class="material-icons topbar-icon">exit_to_app</i>
                    </span>
                    <span>{{ __('ui.nav.logout') }}</span>
                </button>
            </form>
        </div>
    </div>
</nav>

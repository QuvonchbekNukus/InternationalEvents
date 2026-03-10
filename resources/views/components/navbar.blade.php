@php
    $currentUser = auth()->user();
    $roleName = $currentUser?->getRoleNames()->first();
    $roleLabel = $roleName ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $roleName)) : 'Foydalanuvchi';
@endphp

<nav class="topbar" data-topbar aria-label="Yuqori navigatsiya">
    <div class="topbar-left">
        <div class="topbar-brand">
            <span class="topbar-title">Milliy Gvardiya</span>
            <span class="topbar-subtitle">Halqaro Aloqalar Tizimi</span>
        </div>
    </div>

    <div class="topbar-center">
        <label class="topbar-search" aria-label="Global qidiruv">
            <i class="material-icons topbar-icon" aria-hidden="true">search</i>
            <input type="text" placeholder="Qidirish..." />
        </label>
    </div>

    <div class="topbar-right">
        <div class="topbar-actions">
            <button
                class="topbar-icon-button topbar-notification"
                type="button"
                data-topbar-notification
                aria-label="Bildirishnomalar"
            >
                <i class="material-icons topbar-icon" aria-hidden="true">notifications</i>
                <span class="topbar-badge">3</span>
            </button>

            <div class="profile-chip" aria-label="Foydalanuvchi profili">
                <span class="profile-avatar" aria-hidden="true">
                    <i class="material-icons topbar-icon">account_circle</i>
                </span>

                <span class="profile-copy">
                    <span class="profile-copy-label">{{ $roleLabel }}</span>
                    <span class="profile-copy-name">{{ $currentUser?->full_name ?? 'Foydalanuvchi' }}</span>
                </span>
            </div>

            <form class="topbar-logout-form" method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="topbar-logout" type="submit" aria-label="Chiqish">
                    <i class="material-icons topbar-icon" aria-hidden="true">logout</i>
                    <span>Chiqish</span>
                </button>
            </form>
        </div>
    </div>
</nav>

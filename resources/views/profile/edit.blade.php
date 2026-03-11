@extends('layouts.dashboard')

@section('title', 'Profil')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">SETTINGS / PROFILE</p>
                <h1 class="page-title">Profil sozlamalari</h1>
                <p class="page-subtitle">
                    Shaxsiy ma'lumotlar va parol xavfsizligini shu sahifada boshqaring.
                </p>
            </div>

            <a class="btn btn--ghost" href="{{ route('dashboard') }}">
                <i class="material-icons" aria-hidden="true">dashboard</i>
                <span>Dashboard</span>
            </a>
        </div>

        <form class="resource-form" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="section-heading">
                <div>
                    <p class="eyebrow">Asosiy ma'lumotlar</p>
                    <h2 class="section-title">Profil ma'lumotlari</h2>
                </div>
            </div>

            <div class="form-grid">
                <label class="field">
                    <span class="field-label">Ism</span>
                    <input
                        id="first_name"
                        name="first_name"
                        type="text"
                        value="{{ old('first_name', $user->first_name) }}"
                        autocomplete="given-name"
                        required
                    >
                    @error('first_name')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="field">
                    <span class="field-label">Otasining ismi</span>
                    <input
                        id="middle_name"
                        name="middle_name"
                        type="text"
                        value="{{ old('middle_name', $user->middle_name) }}"
                        autocomplete="additional-name"
                        required
                    >
                    @error('middle_name')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="field">
                    <span class="field-label">Familiya</span>
                    <input
                        id="last_name"
                        name="last_name"
                        type="text"
                        value="{{ old('last_name', $user->last_name) }}"
                        autocomplete="family-name"
                        required
                    >
                    @error('last_name')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="field">
                    <span class="field-label">Telefon</span>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value="{{ old('phone', $user->phone) }}"
                        autocomplete="tel"
                        required
                    >
                    @error('phone')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">
                    <i class="material-icons" aria-hidden="true">save</i>
                    <span>Saqlash</span>
                </button>
            </div>
        </form>

        <form class="resource-form" method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="section-heading">
                <div>
                    <p class="eyebrow">Xavfsizlik</p>
                    <h2 class="section-title">Parolni yangilash</h2>
                </div>
            </div>

            <div class="form-grid">
                <label class="field field--span-2">
                    <span class="field-label">Joriy parol</span>
                    <input
                        id="current_password"
                        name="current_password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                    @error('current_password', 'updatePassword')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="field">
                    <span class="field-label">Yangi parol</span>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                    >
                    @error('password', 'updatePassword')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </label>

                <label class="field">
                    <span class="field-label">Parolni tasdiqlang</span>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                    >
                </label>
            </div>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">
                    <i class="material-icons" aria-hidden="true">lock_reset</i>
                    <span>Parolni yangilash</span>
                </button>
            </div>
        </form>

        <section class="content-card" id="profile-notifications">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">Bildirishnomalar</p>
                    <h2 class="section-title">Shaxsiy notificationlar</h2>
                </div>

                <span class="badge">{{ $notifications->total() }} ta</span>
            </div>

            @if ($notifications->count())
                <div class="notification-list">
                    @foreach ($notifications as $notification)
                        <a
                            class="notification-item {{ $notification->is_read ? '' : 'is-unread' }}"
                            href="{{ route('notifications.open', $notification) }}"
                        >
                            <span class="notification-item__icon notification-item__icon--{{ $notification->type ?: 'info' }}" aria-hidden="true">
                                <i class="material-icons">{{ $notification->type_icon }}</i>
                            </span>

                            <span class="notification-item__content">
                                <span class="notification-item__topline">
                                    <span class="notification-item__title">{{ $notification->title }}</span>
                                    <span class="notification-item__meta">{{ $notification->created_at?->diffForHumans() }}</span>
                                </span>
                                <span class="notification-item__message">{{ $notification->message }}</span>
                            </span>

                            <span class="notification-item__aside">
                                <span class="badge">{{ $notification->type_label }}</span>
                                @if (! $notification->is_read)
                                    <span class="notification-item__open">Yangi</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>

                <x-dashboard-pagination :paginator="$notifications" />
            @else
                <div class="table-empty">
                    Hozircha sizga biriktirilgan o'zgarishlar bo'yicha bildirishnomalar yo'q.
                </div>
            @endif
        </section>
    </div>
@endsection

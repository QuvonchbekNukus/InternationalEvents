@extends('layouts.dashboard')

@section('title', "Ruxsatlarni boshqarish")

@section('content')
    <div class="page-section permission-page">
        <div class="page-header">
            <div>
                <p class="eyebrow">SETTINGS / PERMISSIONS</p>
                <h1 class="page-title">Ruxsatlarni boshqarish</h1>
            </div>
        </div>

        <section class="content-card permission-role-overview">
            <p class="eyebrow">BARCHA ROLLAR</p>

            <form class="permission-new-role" method="POST" action="{{ route('role-permissions.store') }}">
                @csrf
                <div class="permission-new-role__row">
                    <label class="permission-new-role__field">
                        <span class="permission-new-role__label">Yangi role</span>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="permission-new-role__input"
                            placeholder="masalan: moderator"
                            autocomplete="off"
                            maxlength="255"
                            pattern="[a-z0-9]+(-[a-z0-9]+)*"
                            title="Kichik lotin harflari, raqam va tire (masalan: yangi-role)"
                            required
                        >
                    </label>
                    <button class="btn btn--secondary permission-new-role__submit" type="submit">
                        <i class="material-icons" aria-hidden="true">add</i>
                        <span>Qo‘shish</span>
                    </button>
                </div>
                @error('name')
                    <p class="form-error permission-new-role__error">{{ $message }}</p>
                @enderror
                <p class="permission-new-role__hint">
                    Faqat kichik lotin harflari, raqam va tire; bo‘sh joy bo‘lmasin (tizim identifikatori).
                </p>
            </form>

            <div class="permission-role-grid">
                @foreach ($roles as $role)
                    @php
                        $roleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $role->name));
                    @endphp
                    <a
                        class="permission-role-card {{ $role->is($selectedRole) ? 'is-active' : '' }}"
                        href="{{ route('role-permissions.index', ['role' => $role->name]) }}"
                    >
                        <span class="permission-role-card__name">{{ $roleLabel }}</span>
                        <span class="permission-role-card__meta">{{ $role->permissions_count }} ta permission</span>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($selectedRole)
            @php
                $selectedRoleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $selectedRole->name));
            @endphp

            <form class="resource-form permission-form" method="POST" action="{{ route('role-permissions.update', $selectedRole) }}">
                @csrf
                @method('PUT')

                <section class="table-card permission-selected-role">
                    <div class="permission-selected-role__inner">
                        <div class="permission-selected-role__header">
                            <div>
                                <p class="eyebrow">TANLANGAN ROLE</p>
                                <h2 class="section-title">{{ $selectedRoleLabel }}</h2>
                            </div>

                            <div class="form-actions">
                                <a class="btn btn--ghost" href="{{ route('role-permissions.index') }}">Yopish</a>
                                <button class="btn btn--primary" type="submit">
                                    <i class="material-icons" aria-hidden="true">save</i>
                                    <span>Saqlash</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="permission-section-grid">
                    @foreach ($permissionSections as $section)
                        <article class="permission-section-card">
                            <div class="permission-section-card__header">
                                <div>
                                    <p class="eyebrow">{{ $section['category'] }}</p>
                                    <h3 class="section-title">{{ $section['label'] }}</h3>
                                </div>

                                <span class="status-pill {{ $section['assigned_count'] ? 'is-active' : 'is-muted' }}">
                                    {{ $section['assigned_count'] }} / {{ count($section['permissions']) }}
                                </span>
                            </div>

                            <div class="permission-toggle-list">
                                @foreach ($section['permissions'] as $permission)
                                    <label class="permission-toggle {{ $permission['assigned'] ? 'is-assigned' : '' }} {{ $permission['protected'] ? 'is-protected' : '' }}">
                                        <div class="permission-toggle__content">
                                            <div class="permission-toggle__topline">
                                                <span class="permission-toggle__title">{{ $permission['action_label'] }}</span>
                                                @if ($permission['protected'])
                                                    <span class="badge">Majburiy</span>
                                                @endif
                                            </div>
                                            <code class="permission-toggle__name">{{ $permission['name'] }}</code>
                                        </div>

                                        <div class="permission-toggle__control">
                                            <input
                                                type="checkbox"
                                                name="permissions[]"
                                                value="{{ $permission['name'] }}"
                                                @checked($permission['assigned'])
                                                @disabled($permission['protected'])
                                            >
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="form-actions">
                    <a class="btn btn--ghost" href="{{ route('role-permissions.index') }}">Yopish</a>
                    <button class="btn btn--primary" type="submit">
                        <i class="material-icons" aria-hidden="true">check_circle</i>
                        <span>Yangilash</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
@endsection

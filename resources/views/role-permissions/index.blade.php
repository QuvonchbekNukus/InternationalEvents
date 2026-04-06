@extends('layouts.dashboard')

@section('title', __('ui.role_permissions.page_title'))

@php
    $rpJsPath = public_path('js/role-permissions.js');
    $rpJsVersion = is_file($rpJsPath) ? filemtime($rpJsPath) : 1;
@endphp

@push('scripts')
    <script src="{{ asset('js/role-permissions.js') }}?v={{ $rpJsVersion }}" defer></script>
@endpush

@section('content')
    <div class="page-section permission-page">
        <div class="page-header permission-wb-page-header">
            <div>
                <p class="eyebrow">{{ __('ui.role_permissions.categories.settings') }}</p>
                <h1 class="page-title">{{ __('ui.role_permissions.page_title') }}</h1>
                <p class="page-subtitle">
                    {{ __('ui.role_permissions.subtitle') }}
                </p>
            </div>
        </div>

        <div class="permission-workbench">
            <aside class="permission-wb-sidebar content-card" aria-label="{{ __('ui.role_permissions.sidebar_aria') }}">
                <header class="permission-wb-sidebar__head">
                    <div>
                        <p class="eyebrow">{{ __('ui.role_permissions.roles_eyebrow') }}</p>
                        <h2 class="permission-wb-sidebar__title">{{ __('ui.role_permissions.pick_role') }}</h2>
                    </div>
                    <button
                        type="button"
                        class="btn btn--primary permission-wb-fab"
                        data-rp-open="create"
                        aria-haspopup="dialog"
                    >
                        <i class="material-icons" aria-hidden="true">add</i>
                        <span>{{ __('ui.role_permissions.new_role') }}</span>
                    </button>
                </header>

                <nav class="permission-wb-nav" aria-label="{{ __('ui.role_permissions.roles_nav_aria') }}">
                    @foreach ($roles as $role)
                        @php
                            $roleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $role->name));
                            $roleIcon = match (true) {
                                $role->name === 'super-admin' => 'verified_user',
                                str_contains($role->name, 'admin') => 'settings',
                                default => 'person',
                            };
                        @endphp
                        <a
                            class="permission-wb-nav__item {{ $selectedRole && $selectedRole->is($role) ? 'is-active' : '' }}"
                            href="{{ route('role-permissions.index', ['role' => $role->name]) }}"
                        >
                            <span class="permission-wb-nav__icon" aria-hidden="true">
                                <i class="material-icons">{{ $roleIcon }}</i>
                            </span>
                            <span class="permission-wb-nav__body">
                                <span class="permission-wb-nav__name">{{ $roleLabel }}</span>
                                <span class="permission-wb-nav__slug">{{ $role->name }}</span>
                            </span>
                            <span class="permission-wb-nav__badge" title="{{ __('ui.role_permissions.perm_count_title') }}">{{ $role->permissions_count }}</span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="permission-wb-main">
                @if ($selectedRole)
                    @php
                        $selectedRoleLabel = \Illuminate\Support\Str::headline(str_replace('-', ' ', $selectedRole->name));
                        $heroIcon = match (true) {
                            $selectedRole->name === 'super-admin' => 'verified_user',
                            str_contains($selectedRole->name, 'admin') => 'settings',
                            default => 'person',
                        };
                    @endphp

                    <header class="permission-wb-hero table-card">
                        <div class="permission-wb-hero__brand">
                            <span class="permission-wb-hero__icon" aria-hidden="true">
                                <i class="material-icons">{{ $heroIcon }}</i>
                            </span>
                            <div class="permission-wb-hero__text">
                                <p class="eyebrow">{{ __('ui.role_permissions.selected_role') }}</p>
                                <h2 class="permission-wb-hero__title">{{ $selectedRoleLabel }}</h2>
                                <div class="permission-wb-hero__meta">
                                    <span class="permission-wb-chip">{{ $selectedRole->name }}</span>
                                    <span class="permission-wb-chip permission-wb-chip--muted">
                                        {{ $totalPermissions }} {{ __('ui.role_permissions.perm_types_count') }}
                                    </span>
                                    <span class="permission-wb-chip permission-wb-chip--muted">
                                        {{ $selectedRole->permissions_count }} {{ __('ui.role_permissions.perm_assigned_count') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="permission-wb-hero__actions">
                            @if ($selectedRole->name !== 'super-admin')
                                <button
                                    type="button"
                                    class="btn btn--secondary permission-wb-action-btn"
                                    data-rp-open="rename"
                                >
                                    <i class="material-icons" aria-hidden="true">edit</i>
                                    <span>{{ __('ui.role_permissions.rename_role') }}</span>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn--danger-outline permission-wb-action-btn"
                                    data-rp-open="delete"
                                >
                                    <i class="material-icons" aria-hidden="true">delete</i>
                                    <span>{{ __('ui.role_permissions.delete_role') }}</span>
                                </button>
                            @endif
                            <a class="btn btn--ghost permission-wb-action-btn" href="{{ route('role-permissions.index') }}">
                                <i class="material-icons" aria-hidden="true">menu</i>
                                <span>{{ __('ui.role_permissions.list') }}</span>
                            </a>
                        </div>
                    </header>

                    @if ($selectedRole->name === 'super-admin')
                        <div class="permission-wb-banner permission-wb-banner--info table-card">
                            <i class="material-icons" aria-hidden="true">info</i>
                            <div>
                                <strong>{{ __('ui.role_permissions.system_role') }}</strong>
                                <p>{{ __('ui.role_permissions.system_role_hint') }}</p>
                            </div>
                        </div>
                    @endif

                    <form
                        class="resource-form permission-form permission-wb-perms"
                        method="POST"
                        action="{{ route('role-permissions.update', $selectedRole) }}"
                        id="permission-wb-perms-form"
                    >
                        @csrf
                        @method('PUT')

                        <div class="permission-section-grid">
                            @foreach ($permissionSections as $section)
                                <article class="permission-section-card">
                                    <div class="permission-section-card__header">
                                        <div>
                                            <p class="eyebrow">{{ $section['category'] }}</p>
                                            <h3 class="section-title">{{ $section['label'] }}</h3>
                                        </div>

                                        <span class="permission-wb-section-pill {{ $section['assigned_count'] ? 'is-on' : '' }}">
                                            {{ $section['assigned_count'] }} / {{ count($section['permissions']) }}
                                        </span>
                                    </div>

                                    <div class="permission-toggle-list">
                                        @foreach ($section['permissions'] as $permission)
                                            <label
                                                class="permission-toggle {{ $permission['assigned'] ? 'is-assigned' : '' }} {{ $permission['protected'] ? 'is-protected' : '' }}"
                                            >
                                                <div class="permission-toggle__content">
                                                    <div class="permission-toggle__topline">
                                                        <span class="permission-toggle__title">{{ $permission['action_label'] }}</span>
                                                        @if ($permission['protected'])
                                                            <span class="badge">{{ __('ui.role_permissions.badge_required') }}</span>
                                                        @endif
                                                    </div>
                                                    <code class="permission-toggle__name">{{ $permission['name'] }}</code>
                                                    <p class="permission-toggle__hint">{{ $permission['description'] }}</p>
                                                </div>

                                                <span class="permission-switch">
                                                    <input
                                                        type="checkbox"
                                                        name="permissions[]"
                                                        value="{{ $permission['name'] }}"
                                                        @checked($permission['assigned'])
                                                        @disabled($permission['protected'])
                                                    >
                                                    <span class="permission-switch__ui" aria-hidden="true"></span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="permission-wb-savebar">
                            <p class="permission-wb-savebar__hint">
                                {{ __('ui.role_permissions.save_hint') }}
                            </p>
                            <div class="permission-wb-savebar__actions">
                                <a class="btn btn--ghost permission-wb-savebar__btn" href="{{ route('role-permissions.index', ['role' => $selectedRole->name]) }}">
                                    <i class="material-icons" aria-hidden="true">close</i>
                                    <span>{{ __('ui.common.actions.cancel') }}</span>
                                </a>
                                <button class="btn btn--primary permission-wb-savebar__btn" type="submit">
                                    <i class="material-icons" aria-hidden="true">save</i>
                                    <span>{{ __('ui.role_permissions.save_permissions') }}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="permission-wb-empty content-card">
                        <span class="permission-wb-empty__icon" aria-hidden="true">
                            <i class="material-icons">touch_app</i>
                        </span>
                        <h2 class="permission-wb-empty__title">{{ __('ui.role_permissions.empty_title') }}</h2>
                        <p class="permission-wb-empty__text">
                            {!! __('ui.role_permissions.empty_text', ['button' => '<strong>'.e(__('ui.role_permissions.new_role')).'</strong>']) !!}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Modallar --}}
        <dialog
            id="rp-d-create"
            class="rp-dialog"
            aria-labelledby="rp-d-create-title"
            @if ($errors->has('name')) data-rp-open-on-load @endif
        >
            <div class="rp-dialog__surface">
                <header class="rp-dialog__header">
                    <div>
                        <p class="rp-dialog__eyebrow">{{ __('ui.role_permissions.dialog.create_eyebrow') }}</p>
                        <h2 id="rp-d-create-title" class="rp-dialog__title">{{ __('ui.role_permissions.dialog.create_title') }}</h2>
                        <p class="rp-dialog__lede">
                            {{ __('ui.role_permissions.dialog.create_lede') }}
                        </p>
                    </div>
                    <button type="button" class="rp-dialog__x" data-rp-close aria-label="{{ __('ui.role_permissions.dialog.close') }}">
                        <i class="material-icons" aria-hidden="true">close</i>
                    </button>
                </header>
                <form method="POST" action="{{ route('role-permissions.store') }}" class="rp-dialog__form">
                    @csrf
                    <label class="rp-field">
                        <span class="rp-field__label">{{ __('ui.role_permissions.dialog.identifier') }}</span>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            class="rp-field__input"
                            placeholder="{{ __('ui.role_permissions.dialog.identifier_placeholder') }}"
                            autocomplete="off"
                            maxlength="255"
                            pattern="[a-z0-9]+(-[a-z0-9]+)*"
                            title="{{ __('ui.role_permissions.dialog.identifier_pattern_title') }}"
                            required
                        >
                        @error('name')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        <span class="rp-field__help">{!! __('ui.role_permissions.dialog.identifier_help') !!}</span>
                    </label>
                    <footer class="rp-dialog__footer">
                        <button type="button" class="btn btn--ghost" data-rp-close>{{ __('ui.common.actions.cancel') }}</button>
                        <button type="submit" class="btn btn--primary">
                            <i class="material-icons" aria-hidden="true">check</i>
                            <span>{{ __('ui.role_permissions.dialog.submit_create') }}</span>
                        </button>
                    </footer>
                </form>
            </div>
        </dialog>

        @if ($selectedRole && $selectedRole->name !== 'super-admin')
            <dialog
                id="rp-d-rename"
                class="rp-dialog"
                aria-labelledby="rp-d-rename-title"
                @if ($errors->has('new_name')) data-rp-open-on-load @endif
            >
                <div class="rp-dialog__surface">
                    <header class="rp-dialog__header">
                        <div>
                            <p class="rp-dialog__eyebrow">{{ __('ui.role_permissions.dialog.rename_eyebrow') }}</p>
                            <h2 id="rp-d-rename-title" class="rp-dialog__title">{{ __('ui.role_permissions.dialog.rename_title') }}</h2>
                            <p class="rp-dialog__lede">
                                {{ __('ui.role_permissions.dialog.rename_lede') }}
                            </p>
                        </div>
                        <button type="button" class="rp-dialog__x" data-rp-close aria-label="{{ __('ui.role_permissions.dialog.close') }}">
                            <i class="material-icons" aria-hidden="true">close</i>
                        </button>
                    </header>
                    <form
                        method="POST"
                        action="{{ route('role-permissions.rename', $selectedRole) }}"
                        class="rp-dialog__form"
                    >
                        @csrf
                        @method('PATCH')
                        <label class="rp-field">
                            <span class="rp-field__label">{{ __('ui.role_permissions.dialog.new_identifier') }}</span>
                            <input
                                type="text"
                                name="new_name"
                                value="{{ old('new_name', $selectedRole->name) }}"
                                class="rp-field__input"
                                maxlength="255"
                                pattern="[a-z0-9]+(-[a-z0-9]+)*"
                                title="{{ __('ui.role_permissions.dialog.identifier_pattern_title') }}"
                                required
                            >
                            @error('new_name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </label>
                        <footer class="rp-dialog__footer">
                            <button type="button" class="btn btn--ghost" data-rp-close>{{ __('ui.common.actions.cancel') }}</button>
                            <button type="submit" class="btn btn--primary">
                                <span>{{ __('ui.common.actions.save') }}</span>
                            </button>
                        </footer>
                    </form>
                </div>
            </dialog>

            <dialog id="rp-d-delete" class="rp-dialog rp-dialog--danger" aria-labelledby="rp-d-delete-title">
                <div class="rp-dialog__surface">
                    <header class="rp-dialog__header">
                        <div>
                            <p class="rp-dialog__eyebrow">{{ __('ui.role_permissions.dialog.delete_eyebrow') }}</p>
                            <h2 id="rp-d-delete-title" class="rp-dialog__title">{{ __('ui.role_permissions.dialog.delete_title') }}</h2>
                            <p class="rp-dialog__lede">
                                {!! __('ui.role_permissions.dialog.delete_lede', ['role' => '<strong>'.e($selectedRoleLabel).'</strong>']) !!}
                            </p>
                        </div>
                        <button type="button" class="rp-dialog__x" data-rp-close aria-label="{{ __('ui.role_permissions.dialog.close') }}">
                            <i class="material-icons" aria-hidden="true">close</i>
                        </button>
                    </header>
                    <form
                        method="POST"
                        action="{{ route('role-permissions.destroy', $selectedRole) }}"
                        class="rp-dialog__form"
                    >
                        @csrf
                        @method('DELETE')
                        <footer class="rp-dialog__footer">
                            <button type="button" class="btn btn--ghost" data-rp-close>{{ __('ui.common.actions.cancel') }}</button>
                            <button type="submit" class="btn btn--danger">
                                <i class="material-icons" aria-hidden="true">delete_forever</i>
                                <span>{{ __('ui.role_permissions.dialog.delete_confirm') }}</span>
                            </button>
                        </footer>
                    </form>
                </div>
            </dialog>
        @endif
    </div>
@endsection

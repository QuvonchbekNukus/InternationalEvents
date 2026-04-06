@extends('layouts.dashboard')

@section('title', __('ui.sidebar.users'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.users')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.users') }}</h1>
                <p class="page-subtitle">
                    {{ __('ui.pages.users.index.subtitle') }}
                </p>
            </div>

            @can('create users')
                <a class="btn btn--primary" href="{{ route('users.create') }}">
                    <i class="material-icons" aria-hidden="true">person_add</i>
                    <span>{{ __('ui.pages.users.index.new') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('users.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.users.index.search_aria') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.users.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="role" aria-label="{{ __('ui.pages.users.index.role_filter') }}">
                <option value="">{{ __('ui.pages.users.index.all_roles') }}</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $role)) }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="department_id" aria-label="{{ __('ui.pages.users.index.department_filter') }}">
                <option value="">{{ __('ui.pages.users.index.all_departments') }}</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) $filters['department_id'] === (string) $department->id)>{{ $department->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="{{ __('ui.pages.users.index.status_filter') }}">
                <option value="">{{ __('ui.pages.users.index.all_statuses') }}</option>
                <option value="active" @selected($filters['status'] === 'active')>{{ __('ui.pages.users.index.status_active') }}</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>{{ __('ui.pages.users.index.status_inactive') }}</option>
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('users.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($users->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.users.index.headers.user') }}</th>
                            <th>{{ __('ui.pages.users.index.headers.department') }}</th>
                            <th>{{ __('ui.pages.users.index.headers.rank') }}</th>
                            <th>{{ __('ui.pages.users.index.headers.role') }}</th>
                            <th>{{ __('ui.pages.users.index.headers.state') }}</th>
                            <th>{{ __('ui.pages.users.index.headers.last_login') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $user->full_name }}</span>
                                    <span class="row-subtitle">{{ $user->phone }}{{ $user->display_position ? ' - '.$user->display_position : '' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $user->department?->display_name ?? __('ui.common.values.unassigned') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $user->rank?->display_name ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge">
                                        {{ $user->roles->first()?->name ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $user->roles->first()->name)) : __('ui.pages.users.index.no_role') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $user->is_active ? 'is-active' : 'is-muted' }}">
                                        {{ $user->is_active ? __('ui.pages.users.index.status_active') : __('ui.pages.users.index.status_inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $user->last_login_at?->format('d.m.Y H:i') ?? __('ui.pages.users.index.no_login') }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @canany(['edit users', 'edit own users'])
                                            <a class="action-pill" href="{{ route('users.edit', $user) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcanany

                                        @can('delete users')
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm(@json(__('ui.pages.users.index.confirm_delete')));">
                                                @csrf
                                                @method('DELETE')

                                                <button class="action-pill action-pill--danger" type="submit">
                                                    <i class="material-icons" aria-hidden="true">delete</i>
                                                    <span>{{ __('ui.common.actions.delete') }}</span>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="table-empty">
                    {{ __('ui.pages.users.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$users" />
        </div>
    </div>
@endsection

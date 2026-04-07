@extends('layouts.dashboard')

@section('title', __('ui.sidebar.departments'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.departments')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.departments') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.departments.index.subtitle') }}</p>
            </div>

            @can('create departments')
                <a class="btn btn--primary" href="{{ route('departments.create') }}">
                    <i class="material-icons" aria-hidden="true">business_center</i>
                    <span>{{ __('ui.pages.departments.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('departments.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.departments.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.pages.departments.index.search_placeholder') }}">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.pages.departments.index.search_action') }}</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('departments.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($departments->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.departments.index.headers.department') }}</th>
                            <th>{{ __('ui.pages.departments.index.headers.code') }}</th>
                            <th>{{ __('ui.pages.departments.index.headers.description') }}</th>
                            <th>{{ __('ui.pages.departments.index.headers.users') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $department)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $department->display_name }}</span>
                                    <span class="row-subtitle">{{ $department->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ $department->code ?: __('ui.pages.departments.index.values.code_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $department->description ?: __('ui.common.values.no_description') }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ __('ui.pages.departments.index.values.user_count', ['count' => $department->users_count]) }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit departments')
                                            <a class="action-pill" href="{{ route('departments.edit', $department) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete departments')
                                            <form
                                                method="POST"
                                                action="{{ route('departments.destroy', $department) }}"
                                                data-confirm-message="{{ __('ui.pages.departments.index.confirm_delete') }}"
                                                onsubmit="return confirm(this.dataset.confirmMessage);"
                                            >
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
                    {{ __('ui.pages.departments.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$departments" />
        </div>
    </div>
@endsection

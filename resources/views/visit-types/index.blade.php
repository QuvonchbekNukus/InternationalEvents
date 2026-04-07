@extends('layouts.dashboard')

@section('title', __('ui.sidebar.visit_types'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.visit_types')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.visit_types') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.visit_types.index.subtitle') }}</p>
            </div>

            @can('create visit types')
                <a class="btn btn--primary" href="{{ route('visit-types.create') }}">
                    <i class="material-icons" aria-hidden="true">note_add</i>
                    <span>{{ __('ui.pages.visit_types.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('visit-types.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.visit_types.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.pages.visit_types.index.search_placeholder') }}">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.pages.visit_types.index.search_action') }}</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('visit-types.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($visitTypes->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.visit_types.index.headers.name_uz') }}</th>
                            <th>{{ __('ui.pages.visit_types.index.headers.name_ru') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visitTypes as $visitType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $visitType->display_name }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $visitType->name_ru }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit visit types')
                                            <a class="action-pill" href="{{ route('visit-types.edit', $visitType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete visit types')
                                            <form
                                                method="POST"
                                                action="{{ route('visit-types.destroy', $visitType) }}"
                                                data-confirm-message="{{ __('ui.pages.visit_types.index.confirm_delete') }}"
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
                    {{ __('ui.pages.visit_types.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$visitTypes" />
        </div>
    </div>
@endsection

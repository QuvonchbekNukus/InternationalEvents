@extends('layouts.dashboard')

@section('title', __('ui.sidebar.ranks'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.ranks')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.ranks') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.ranks.index.subtitle') }}</p>
            </div>

            @can('create ranks')
                <a class="btn btn--primary is-create-action" href="{{ route('ranks.create') }}">
                    <i class="material-icons" aria-hidden="true">add</i>
                    <span>{{ __('ui.pages.ranks.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('ranks.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.ranks.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.pages.ranks.index.search_placeholder') }}">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.pages.ranks.index.search_action') }}</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('ranks.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($ranks->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.ranks.index.headers.rank') }}</th>
                            <th>{{ __('ui.pages.ranks.index.headers.russian') }}</th>
                            <th>{{ __('ui.pages.ranks.index.headers.users') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ranks as $rank)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $rank->display_name }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $rank->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ __('ui.pages.ranks.index.values.user_count', ['count' => $rank->users_count]) }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit ranks')
                                            <a class="action-pill" href="{{ route('ranks.edit', $rank) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete ranks')
                                            <form
                                                method="POST"
                                                action="{{ route('ranks.destroy', $rank) }}"
                                                data-confirm-message="{{ __('ui.pages.ranks.index.confirm_delete') }}"
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
                    {{ __('ui.pages.ranks.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$ranks" />
        </div>
    </div>
@endsection

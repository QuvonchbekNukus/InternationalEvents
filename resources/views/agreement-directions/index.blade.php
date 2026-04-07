@extends('layouts.dashboard')

@section('title', __('ui.sidebar.agreement_directions'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreement_directions')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.agreement_directions') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreement_directions.index.subtitle') }}</p>
            </div>

            @can('create agreement directions')
                <a class="btn btn--primary" href="{{ route('agreement-directions.create') }}">
                    <i class="material-icons" aria-hidden="true">playlist_add</i>
                    <span>{{ __('ui.pages.agreement_directions.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('agreement-directions.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.agreement_directions.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.pages.agreement_directions.index.search_placeholder') }}">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.pages.agreement_directions.index.search_action') }}</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('agreement-directions.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($agreementDirections->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.agreement_directions.index.headers.name_uz') }}</th>
                            <th>{{ __('ui.pages.agreement_directions.index.headers.name_ru') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agreementDirections as $agreementDirection)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $agreementDirection->display_name }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $agreementDirection->name_ru }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit agreement directions')
                                            <a class="action-pill" href="{{ route('agreement-directions.edit', $agreementDirection) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete agreement directions')
                                            <form
                                                method="POST"
                                                action="{{ route('agreement-directions.destroy', $agreementDirection) }}"
                                                data-confirm-message="{{ __('ui.pages.agreement_directions.index.confirm_delete') }}"
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
                    {{ __('ui.pages.agreement_directions.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$agreementDirections" />
        </div>
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', __('ui.sidebar.agreement_types'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreement_types')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.agreement_types') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreement_types.index.subtitle') }}</p>
            </div>

            @can('create agreement types')
                <a class="btn btn--primary" href="{{ route('agreement-types.create') }}">
                    <i class="material-icons" aria-hidden="true">note_add</i>
                    <span>{{ __('ui.pages.agreement_types.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('agreement-types.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.agreement_types.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.pages.agreement_types.index.search_placeholder') }}">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.pages.agreement_types.index.search_action') }}</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('agreement-types.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($agreementTypes->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.agreement_types.index.headers.name_uz') }}</th>
                            <th>{{ __('ui.pages.agreement_types.index.headers.name_ru') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agreementTypes as $agreementType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $agreementType->display_name }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $agreementType->name_ru }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit agreement types')
                                            <a class="action-pill" href="{{ route('agreement-types.edit', $agreementType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete agreement types')
                                            <form
                                                method="POST"
                                                action="{{ route('agreement-types.destroy', $agreementType) }}"
                                                data-confirm-message="{{ __('ui.pages.agreement_types.index.confirm_delete') }}"
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
                    {{ __('ui.pages.agreement_types.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$agreementTypes" />
        </div>
    </div>
@endsection

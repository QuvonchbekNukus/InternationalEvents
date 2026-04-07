@extends('layouts.dashboard')

@section('title', __('ui.sidebar.document_types'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.document_types')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.document_types') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.document_types.index.subtitle') }}</p>
            </div>

            @can('create document types')
                <a class="btn btn--primary" href="{{ route('document-types.create') }}">
                    <i class="material-icons" aria-hidden="true">description</i>
                    <span>{{ __('ui.pages.document_types.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('document-types.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.document_types.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('ui.pages.document_types.index.search_placeholder') }}">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.pages.document_types.index.search_action') }}</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('document-types.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($documentTypes->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.document_types.index.headers.name_uz') }}</th>
                            <th>{{ __('ui.pages.document_types.index.headers.name_ru') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documentTypes as $documentType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $documentType->display_name }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $documentType->name_ru }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit document types')
                                            <a class="action-pill" href="{{ route('document-types.edit', $documentType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete document types')
                                            <form
                                                method="POST"
                                                action="{{ route('document-types.destroy', $documentType) }}"
                                                data-confirm-message="{{ __('ui.pages.document_types.index.confirm_delete') }}"
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
                    {{ __('ui.pages.document_types.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$documentTypes" />
        </div>
    </div>
@endsection

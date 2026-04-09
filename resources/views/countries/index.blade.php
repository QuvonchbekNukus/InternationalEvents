@extends('layouts.dashboard')

@section('title', __('ui.sidebar.countries'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.countries')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.countries') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.countries.index.subtitle') }}</p>
            </div>

            @can('create countries')
                <a class="btn btn--primary" href="{{ route('countries.create') }}">
                    <i class="material-icons" aria-hidden="true">public</i>
                    <span>{{ __('ui.pages.countries.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('countries.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.countries.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.countries.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="status" aria-label="{{ __('ui.pages.countries.index.status_filter') }}">
                <option value="">{{ __('ui.pages.countries.index.all_statuses') }}</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('countries.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($countries->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.countries.index.headers.country') }}</th>
                            <th>{{ __('ui.pages.countries.index.headers.codes') }}</th>
                            <th>{{ __('ui.pages.countries.index.headers.region') }}</th>
                            <th>{{ __('ui.pages.countries.index.headers.partnership_history') }}</th>
                            <th>{{ __('ui.pages.countries.index.headers.status') }}</th>
                            <th>{{ __('ui.pages.countries.index.headers.files') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($countries as $country)
                            @php
                                $statusClass = match ($country->cooperation_status) {
                                    'rejada' => 'is-planned',
                                    'tugatilgan' => 'is-completed',
                                    default => 'is-active',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">
                                        <a class="row-title-link" href="{{ route('countries.show', $country) }}">{{ $country->display_name }}</a>
                                    </span>
                                    <span class="row-subtitle">{{ $country->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ $country->iso2 ?: '--' }} / {{ $country->iso3 ?: '---' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $country->display_region ?: __('ui.pages.countries.index.values.region_missing') }}</span>
                                    @if ($country->notes)
                                        <span class="row-subtitle">{{ $country->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($country->partnershipHistoryDocument)
                                        <span class="row-title">{{ $country->partnershipHistoryDocument->file_name }}</span>
                                        @if (auth()->user()?->can('view documents') || auth()->user()?->can('view own documents'))
                                            <a class="row-subtitle row-title-link" href="{{ route('documents.download', $country->partnershipHistoryDocument) }}">
                                                {{ __('ui.common.actions.download_file') }}
                                            </a>
                                        @endif
                                    @else
                                        <span class="row-title">{{ __('ui.pages.countries.index.values.partnership_history_missing') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$country->cooperation_status] ?? $country->cooperation_status }}
                                    </span>
                                </td>
                                <td>
                                    @if ($country->has_flag_file)
                                        <img
                                            class="country-flag-preview"
                                            src="{{ asset($country->flag_asset_path) }}"
                                            alt="{{ __('ui.pages.countries.index.values.flag_alt', ['country' => $country->display_name]) }}"
                                        >
                                    @endif
                                    <span class="row-subtitle">{{ $country->flag_asset_path ?: __('ui.pages.countries.index.values.iso_missing') }}</span>
                                    <span class="row-subtitle">{{ $country->boundary_geojson_path ?: __('ui.pages.countries.index.values.geojson_missing') }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit countries')
                                            <a class="action-pill" href="{{ route('countries.edit', $country) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete countries')
                                            <form method="POST" action="{{ route('countries.destroy', $country) }}" data-confirm-message="{{ __('ui.pages.countries.index.confirm_delete') }}" onsubmit="return confirm(this.dataset.confirmMessage);">
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
                    {{ __('ui.pages.countries.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$countries" />
        </div>
    </div>
@endsection

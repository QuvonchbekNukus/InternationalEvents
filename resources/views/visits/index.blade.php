@extends('layouts.dashboard')

@section('title', __('ui.sidebar.all_visits'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.visits')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.all_visits') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.visits.index.subtitle') }}</p>
            </div>

            @can('create visits')
                <a class="btn btn--primary" href="{{ route('visits.create') }}">
                    <i class="material-icons" aria-hidden="true">flight_takeoff</i>
                    <span>{{ __('ui.pages.visits.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('visits.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.visits.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.visits.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="{{ __('ui.pages.visits.index.country_filter') }}">
                <option value="">{{ __('ui.pages.visits.index.all_countries') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="visit_type_id" aria-label="{{ __('ui.pages.visits.index.type_filter') }}">
                <option value="">{{ __('ui.pages.visits.index.all_types') }}</option>
                @foreach ($visitTypes as $visitType)
                    <option value="{{ $visitType->id }}" @selected((string) $filters['visit_type_id'] === (string) $visitType->id)>{{ $visitType->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="direction" aria-label="{{ __('ui.pages.visits.index.direction_filter') }}">
                <option value="">{{ __('ui.pages.visits.index.all_directions') }}</option>
                @foreach ($directions as $directionValue => $directionLabel)
                    <option value="{{ $directionValue }}" @selected($filters['direction'] === $directionValue)>{{ $directionLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="{{ __('ui.pages.visits.index.status_filter') }}">
                <option value="">{{ __('ui.pages.visits.index.all_statuses') }}</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('visits.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($visits->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.visits.index.headers.visit') }}</th>
                            <th>{{ __('ui.pages.visits.index.headers.country_org') }}</th>
                            <th>{{ __('ui.pages.visits.index.headers.type_direction') }}</th>
                            <th>{{ __('ui.pages.visits.index.headers.address') }}</th>
                            <th>{{ __('ui.pages.visits.index.headers.duration') }}</th>
                            <th>{{ __('ui.pages.visits.index.headers.responsible') }}</th>
                            <th>{{ __('ui.pages.visits.index.headers.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visits as $visit)
                            @php
                                $statusClass = match ($visit->status) {
                                    'ongoing' => 'is-active',
                                    'completed' => 'is-completed',
                                    'planned' => 'is-planned',
                                    default => 'is-muted',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $visit->display_title }}</span>
                                    <span class="row-subtitle">{{ $visit->title_ru }}{{ $visit->title_cryl ? ' / '.$visit->title_cryl : '' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->country?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $visit->partnerOrganization?->display_name ?: __('ui.pages.visits.index.values.organization_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->visitType?->display_name ?: __('ui.pages.visits.index.values.type_missing') }}</span>
                                    <span class="row-subtitle">{{ $directions[$visit->direction] ?? __('ui.pages.visits.index.values.direction_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->city ?: __('ui.pages.visits.index.values.city_missing') }}</span>
                                    <span class="row-subtitle">{{ $visit->address ?: __('ui.pages.visits.index.values.address_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->start_date?->format('d.m.Y') }}</span>
                                    <span class="row-subtitle">{{ $visit->end_date?->format('d.m.Y') ?: __('ui.pages.visits.index.values.end_date_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->responsibleUser?->full_name ?: __('ui.pages.visits.index.values.responsible_missing') }}</span>
                                    <span class="row-subtitle">{{ $visit->responsibleDepartment?->display_name ?: __('ui.pages.visits.index.values.department_missing') }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$visit->status] ?? $visit->status }}
                                    </span>
                                    @if ($visit->display_purpose || $visit->description)
                                        <span class="row-subtitle">{{ \Illuminate\Support\Str::limit($visit->display_purpose ?: $visit->description, 90) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @canany(['edit visits', 'edit own visits'])
                                            <a class="action-pill" href="{{ route('visits.edit', $visit) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcanany

                                        @can('delete visits')
                                            <form method="POST" action="{{ route('visits.destroy', $visit) }}" onsubmit="return confirm(@js(__('ui.pages.visits.index.confirm_delete')));">
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
                    {{ __('ui.pages.visits.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$visits" />
        </div>
    </div>
@endsection

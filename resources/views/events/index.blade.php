@extends('layouts.dashboard')

@section('title', __('ui.sidebar.all_events'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.events')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.all_events') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.events.index.subtitle') }}</p>
            </div>

            @can('create events')
                <a class="btn btn--primary" href="{{ route('events.create') }}">
                    <i class="material-icons" aria-hidden="true">event</i>
                    <span>{{ __('ui.pages.events.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('events.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.events.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.events.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="{{ __('ui.pages.events.index.country_filter') }}">
                <option value="">{{ __('ui.pages.events.index.all_countries') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="event_type_id" aria-label="{{ __('ui.pages.events.index.type_filter') }}">
                <option value="">{{ __('ui.pages.events.index.all_types') }}</option>
                @foreach ($eventTypes as $eventType)
                    <option value="{{ $eventType->id }}" @selected((string) $filters['event_type_id'] === (string) $eventType->id)>{{ $eventType->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="format" aria-label="{{ __('ui.pages.events.index.format_filter') }}">
                <option value="">{{ __('ui.pages.events.index.all_formats') }}</option>
                @foreach ($formats as $formatValue => $formatLabel)
                    <option value="{{ $formatValue }}" @selected($filters['format'] === $formatValue)>{{ $formatLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="{{ __('ui.pages.events.index.status_filter') }}">
                <option value="">{{ __('ui.pages.events.index.all_statuses') }}</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('events.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($events->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.events.index.headers.event') }}</th>
                            <th>{{ __('ui.pages.events.index.headers.country_org') }}</th>
                            <th>{{ __('ui.pages.events.index.headers.type_format') }}</th>
                            <th>{{ __('ui.pages.events.index.headers.time') }}</th>
                            <th>{{ __('ui.pages.events.index.headers.responsible') }}</th>
                            <th>{{ __('ui.pages.events.index.headers.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            @php
                                $statusClass = match ($event->status) {
                                    'hozirda' => 'is-active',
                                    'tugatilgan' => 'is-completed',
                                    'rejada' => 'is-planned',
                                    default => 'is-muted',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $event->display_title }}</span>
                                    <span class="row-subtitle">{{ $event->title_ru }}{{ $event->title_cryl ? ' / '.$event->title_cryl : '' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $event->country?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $event->partnerOrganization?->display_name ?: __('ui.pages.events.index.values.organization_missing') }}</span>
                                    @if ($event->agreement)
                                        <span class="row-subtitle">{{ $event->agreement->display_title }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $event->eventType?->display_name ?: __('ui.pages.events.index.values.type_missing') }}</span>
                                    <span class="row-subtitle">{{ $formats[$event->format] ?? $event->format }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $event->start_datetime?->format('d.m.Y H:i') }}</span>
                                    <span class="row-subtitle">{{ $event->end_datetime?->format('d.m.Y H:i') ?: __('ui.pages.events.index.values.end_time_missing') }}</span>
                                    <span class="row-subtitle">{{ $event->city ?: __('ui.pages.events.index.values.city_missing') }}{{ $event->address ? ' / '.$event->address : '' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $event->responsibleUser?->full_name ?: __('ui.pages.events.index.values.responsible_missing') }}</span>
                                    <span class="row-subtitle">{{ $event->responsibleDepartment?->display_name ?: __('ui.pages.events.index.values.department_missing') }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$event->status] ?? $event->status }}
                                    </span>
                                    @if ($event->control_due_date)
                                        <span class="row-subtitle">{{ __('ui.pages.events.index.values.control') }}: {{ $event->control_due_date->format('d.m.Y') }}</span>
                                    @endif
                                    @if ($event->description)
                                        <span class="row-subtitle">{{ \Illuminate\Support\Str::limit($event->description, 90) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @canany(['edit events', 'edit own events'])
                                            <a class="action-pill" href="{{ route('events.edit', $event) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcanany

                                        @can('delete events')
                                            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm(@js(__('ui.pages.events.index.confirm_delete')));">
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
                    {{ __('ui.pages.events.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$events" />
        </div>
    </div>
@endsection

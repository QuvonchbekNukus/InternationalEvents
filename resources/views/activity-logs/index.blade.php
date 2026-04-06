@extends('layouts.dashboard')

@section('title', __('ui.activity_log.page_title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.activity_log.eyebrow') }}</p>
                <h1 class="page-title">{{ __('ui.activity_log.page_title') }}</h1>
                <p class="page-subtitle">{{ __('ui.activity_log.subtitle') }}</p>
            </div>
        </div>

        <form class="toolbar" method="GET" action="{{ route('activity-logs.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.activity_log.search_aria') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.activity_log.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="causer_id" aria-label="{{ __('ui.activity_log.filter_user') }}">
                <option value="">{{ __('ui.activity_log.all_users') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) $filters['causer_id'] === (string) $user->id)>{{ $user->full_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="event" aria-label="{{ __('ui.activity_log.filter_event') }}">
                <option value="">{{ __('ui.activity_log.all_events') }}</option>
                @foreach ($eventLabels as $eventValue => $eventLabel)
                    <option value="{{ $eventValue }}" @selected($filters['event'] === $eventValue)>{{ $eventLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="subject_type" aria-label="{{ __('ui.activity_log.filter_subject') }}">
                <option value="">{{ __('ui.activity_log.all_subjects') }}</option>
                @foreach ($subjectTypeLabels as $subjectType => $subjectTypeLabel)
                    <option value="{{ $subjectType }}" @selected($filters['subject_type'] === $subjectType)>{{ $subjectTypeLabel }}</option>
                @endforeach
            </select>

            <input class="toolbar-select" type="date" name="from" value="{{ $filters['from'] }}" aria-label="{{ __('ui.activity_log.date_from') }}">
            <input class="toolbar-select" type="date" name="to" value="{{ $filters['to'] }}" aria-label="{{ __('ui.activity_log.date_to') }}">

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter(fn ($value) => $value !== '' && $value !== null)->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('activity-logs.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($activities->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.activity_log.columns.time') }}</th>
                            <th>{{ __('ui.activity_log.columns.user') }}</th>
                            <th>{{ __('ui.activity_log.columns.action') }}</th>
                            <th>{{ __('ui.activity_log.columns.object') }}</th>
                            <th>{{ __('ui.activity_log.columns.detail') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            @php
                                $properties = $activity->properties ?? collect();
                                $subjectLabel = $properties->get('subject_label') ?: ($activity->subject?->display_title ?? $activity->subject?->display_name ?? null);
                                $subjectTypeLabel = $properties->get('subject_type_label') ?: ($subjectTypeLabels[$activity->subject_type] ?? class_basename((string) $activity->subject_type));
                                $causerLabel = $properties->get('causer_name') ?: ($activity->causer?->full_name ?? $activity->causer?->display_name ?? null);
                                $changedAttributes = collect($properties->get('attributes', []))
                                    ->keys()
                                    ->reject(fn ($attribute) => $attribute === 'updated_at')
                                    ->values();
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $activity->created_at?->format('d.m.Y H:i:s') }}</span>
                                    <span class="row-subtitle">{{ $activity->created_at?->diffForHumans() }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $causerLabel ?: __('ui.activity_log.system') }}</span>
                                    <span class="row-subtitle">{{ $properties->get('ip_address') ?: __('ui.activity_log.unknown_ip') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $eventLabels[$activity->event] ?? ($activity->description ?: ($activity->event ?: __('ui.activity_log.unknown_action'))) }}</span>
                                    <span class="row-subtitle">{{ $activity->event ?: 'system' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $subjectLabel ?: __('ui.activity_log.system_event') }}</span>
                                    <span class="row-subtitle">{{ $subjectTypeLabel }}</span>
                                    @if ($activity->subject_id)
                                        <span class="row-subtitle">ID: {{ $activity->subject_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $activity->description }}</span>
                                    @if ($changedAttributes->isNotEmpty())
                                        <span class="row-subtitle">{{ __('ui.activity_log.fields_changed') }}: {{ $changedAttributes->take(6)->implode(', ') }}{{ $changedAttributes->count() > 6 ? '...' : '' }}</span>
                                    @endif
                                    @if ($properties->get('route'))
                                        <span class="row-subtitle">{{ __('ui.activity_log.route') }}: {{ $properties->get('route') }}</span>
                                    @endif
                                    @if ($properties->get('roles'))
                                        <span class="row-subtitle">{{ __('ui.activity_log.roles') }}: {{ $properties->get('roles') }}</span>
                                    @endif
                                    @if ($properties->get('permissions'))
                                        <span class="row-subtitle">{{ __('ui.activity_log.permissions') }}: {{ $properties->get('permissions') }}</span>
                                    @endif
                                    @if ($properties->get('phone_masked'))
                                        <span class="row-subtitle">{{ __('ui.activity_log.phone_masked') }}: {{ $properties->get('phone_masked') }}</span>
                                    @endif
                                    @if ($properties->get('file_name'))
                                        <span class="row-subtitle">{{ __('ui.activity_log.file') }}: {{ $properties->get('file_name') }}</span>
                                    @endif
                                    @if ($properties->get('user_agent'))
                                        <span class="row-subtitle">{{ \Illuminate\Support\Str::limit($properties->get('user_agent'), 90) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="table-empty">
                    {{ __('ui.activity_log.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$activities" />
        </div>
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Tizim loglari')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.sidebar.settings') }} / {{ __('ui.sidebar.activity_logs') }}</p>
                <h1 class="page-title">Tizim loglari</h1>
                <p class="page-subtitle">Kim qachon tizimga kirgani, chiqgani va qaysi obyekt ustida qanday amal bajargani shu yerda ko'rinadi.</p>
            </div>
        </div>

        <form class="toolbar" method="GET" action="{{ route('activity-logs.index') }}">
            <label class="toolbar-search" aria-label="Tizim loglarini qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Foydalanuvchi, obyekt, IP yoki fayl bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="causer_id" aria-label="Foydalanuvchi bo'yicha filter">
                <option value="">Barcha foydalanuvchilar</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected((string) $filters['causer_id'] === (string) $user->id)>{{ $user->full_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="event" aria-label="Amal bo'yicha filter">
                <option value="">Barcha amallar</option>
                @foreach ($eventLabels as $eventValue => $eventLabel)
                    <option value="{{ $eventValue }}" @selected($filters['event'] === $eventValue)>{{ $eventLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="subject_type" aria-label="Obyekt bo'yicha filter">
                <option value="">Barcha obyektlar</option>
                @foreach ($subjectTypeLabels as $subjectType => $subjectTypeLabel)
                    <option value="{{ $subjectType }}" @selected($filters['subject_type'] === $subjectType)>{{ $subjectTypeLabel }}</option>
                @endforeach
            </select>

            <input class="toolbar-select" type="date" name="from" value="{{ $filters['from'] }}" aria-label="Boshlanish sanasi">
            <input class="toolbar-select" type="date" name="to" value="{{ $filters['to'] }}" aria-label="Tugash sanasi">

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>Filtrlash</span>
            </button>

            @if (collect($filters)->filter(fn ($value) => $value !== '' && $value !== null)->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('activity-logs.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($activities->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Vaqt</th>
                            <th>Foydalanuvchi</th>
                            <th>Amal</th>
                            <th>Obyekt</th>
                            <th>Tafsilot</th>
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
                                    <span class="row-title">{{ $causerLabel ?: 'Tizim' }}</span>
                                    <span class="row-subtitle">{{ $properties->get('ip_address') ?: 'IP aniqlanmadi' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $eventLabels[$activity->event] ?? ($activity->description ?: ($activity->event ?: 'Amal')) }}</span>
                                    <span class="row-subtitle">{{ $activity->event ?: 'system' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $subjectLabel ?: 'Tizim hodisasi' }}</span>
                                    <span class="row-subtitle">{{ $subjectTypeLabel }}</span>
                                    @if ($activity->subject_id)
                                        <span class="row-subtitle">ID: {{ $activity->subject_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $activity->description }}</span>
                                    @if ($changedAttributes->isNotEmpty())
                                        <span class="row-subtitle">Maydonlar: {{ $changedAttributes->take(6)->implode(', ') }}{{ $changedAttributes->count() > 6 ? '...' : '' }}</span>
                                    @endif
                                    @if ($properties->get('file_name'))
                                        <span class="row-subtitle">Fayl: {{ $properties->get('file_name') }}</span>
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
                    Hozircha audit log yozuvlari topilmadi.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$activities" />
        </div>
    </div>
@endsection

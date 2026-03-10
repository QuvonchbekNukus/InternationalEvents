@extends('layouts.dashboard')

@section('title', 'Barcha tadbirlar')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Events</p>
                <h1 class="page-title">Barcha tadbirlar</h1>
                <p class="page-subtitle">Tadbirlar ro'yxati, format, holat va mas'ullar bo'yicha nazorat oynasi.</p>
            </div>

            @can('create events')
                <a class="btn btn--primary" href="{{ route('events.create') }}">
                    <i class="material-icons" aria-hidden="true">event</i>
                    <span>Yangi tadbir</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('events.index') }}">
            <label class="toolbar-search" aria-label="Tadbir qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, davlat, tashkilot, kelishuv yoki natija bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="Davlat bo'yicha filter">
                <option value="">Barcha davlatlar</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->name_uz ?: $country->name_ru }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="event_type_id" aria-label="Tadbir turi bo'yicha filter">
                <option value="">Barcha turlar</option>
                @foreach ($eventTypes as $eventType)
                    <option value="{{ $eventType->id }}" @selected((string) $filters['event_type_id'] === (string) $eventType->id)>{{ $eventType->name_uz }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="format" aria-label="Format bo'yicha filter">
                <option value="">Barcha formatlar</option>
                @foreach ($formats as $formatValue => $formatLabel)
                    <option value="{{ $formatValue }}" @selected($filters['format'] === $formatValue)>{{ $formatLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="Holat bo'yicha filter">
                <option value="">Barcha holatlar</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_alt</i>
                <span>Filtrlash</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('events.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($events->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tadbir</th>
                            <th>Davlat / tashkilot</th>
                            <th>Turi / format</th>
                            <th>Vaqti</th>
                            <th>Javobgar</th>
                            <th>Holat</th>
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
                                    <span class="row-subtitle">{{ $event->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}</span>
                                    @if ($event->agreement)
                                        <span class="row-subtitle">{{ $event->agreement->short_title_uz ?: ($event->agreement->title_uz ?: $event->agreement->title_ru) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $event->eventType?->name_uz ?: "Tur biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $formats[$event->format] ?? $event->format }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $event->start_datetime?->format('d.m.Y H:i') }}</span>
                                    <span class="row-subtitle">{{ $event->end_datetime?->format('d.m.Y H:i') ?: "Tugash vaqti yo'q" }}</span>
                                    <span class="row-subtitle">{{ $event->city ?: "Shahar yo'q" }}{{ $event->address ? ' / '.$event->address : '' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $event->responsibleUser?->full_name ?: "Javobgar biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $event->responsibleDepartment?->name_uz ?: "Bo'lim biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$event->status] ?? $event->status }}
                                    </span>
                                    @if ($event->control_due_date)
                                        <span class="row-subtitle">Nazorat: {{ $event->control_due_date->format('d.m.Y') }}</span>
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
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcanany

                                        @can('delete events')
                                            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Ushbu tadbirni o\'chirishni tasdiqlaysizmi?');">
                                                @csrf
                                                @method('DELETE')

                                                <button class="action-pill action-pill--danger" type="submit">
                                                    <i class="material-icons" aria-hidden="true">delete</i>
                                                    <span>O'chirish</span>
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
                    Tadbirlar hali yaratilmagan. Yangi tadbir qo'shing yoki filtrlarni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$events" />
        </div>
    </div>
@endsection

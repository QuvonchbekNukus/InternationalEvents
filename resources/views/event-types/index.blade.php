@extends('layouts.dashboard')

@section('title', __('ui.sidebar.event_types'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.event_types')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.event_types') }}</h1>
                <p class="page-subtitle">Seminar, forum, konferensiya va boshqa tadbir turlarini boshqarish oynasi.</p>
            </div>

            @can('create event types')
                <a class="btn btn--primary" href="{{ route('event-types.create') }}">
                    <i class="material-icons" aria-hidden="true">event_note</i>
                    <span>Yangi tur</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('event-types.index') }}">
            <label class="toolbar-search" aria-label="Tadbir turi qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom bo'yicha qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('event-types.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($eventTypes->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nomi (UZ)</th>
                            <th>Nomi (RU)</th>
                            <th>Nomi (KRYL)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($eventTypes as $eventType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $eventType->display_name }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $eventType->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $eventType->name_cryl }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit event types')
                                            <a class="action-pill" href="{{ route('event-types.edit', $eventType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete event types')
                                            <form method="POST" action="{{ route('event-types.destroy', $eventType) }}" onsubmit="return confirm('Ushbu tadbir turini o\'chirishni tasdiqlaysizmi?');">
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
                    Tadbir turlari hali yaratilmagan. Yangi tur qo'shing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$eventTypes" />
        </div>
    </div>
@endsection

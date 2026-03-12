@extends('layouts.dashboard')

@section('title', __('ui.sidebar.visit_types'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.visit_types')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.visit_types') }}</h1>
                <p class="page-subtitle">Rasmiy, ishchi, do'stona va boshqa tashrif turlarini boshqarish oynasi.</p>
            </div>

            @can('create visit types')
                <a class="btn btn--primary" href="{{ route('visit-types.create') }}">
                    <i class="material-icons" aria-hidden="true">note_add</i>
                    <span>Yangi tur</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('visit-types.index') }}">
            <label class="toolbar-search" aria-label="Tashrif turi qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom bo'yicha qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('visit-types.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($visitTypes->count())
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
                        @foreach ($visitTypes as $visitType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $visitType->display_name }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $visitType->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $visitType->name_cryl }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit visit types')
                                            <a class="action-pill" href="{{ route('visit-types.edit', $visitType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete visit types')
                                            <form method="POST" action="{{ route('visit-types.destroy', $visitType) }}" onsubmit="return confirm('Ushbu tashrif turini o\'chirishni tasdiqlaysizmi?');">
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
                    Tashrif turlari hali yaratilmagan. Yangi tur qo'shing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$visitTypes" />
        </div>
    </div>
@endsection

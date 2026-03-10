@extends('layouts.dashboard')

@section('title', 'Tashkilot turlari')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Organization Types</p>
                <h1 class="page-title">Tashkilot turlari</h1>
                <p class="page-subtitle">Hamkor tashkilotlar yoki subyektlar uchun tur toifalarini boshqarish oynasi.</p>
            </div>

            @can('create organization types')
                <a class="btn btn--primary" href="{{ route('organization-types.create') }}">
                    <i class="material-icons" aria-hidden="true">domain_add</i>
                    <span>Yangi tur</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('organization-types.index') }}">
            <label class="toolbar-search" aria-label="Tashkilot turi qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom bo'yicha qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_alt</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('organization-types.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($organizationTypes->count())
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
                        @foreach ($organizationTypes as $organizationType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $organizationType->name_uz }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $organizationType->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $organizationType->name_cryl }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit organization types')
                                            <a class="action-pill" href="{{ route('organization-types.edit', $organizationType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete organization types')
                                            <form method="POST" action="{{ route('organization-types.destroy', $organizationType) }}" onsubmit="return confirm('Ushbu tashkilot turini ochirishni tasdiqlaysizmi?');">
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
                    Tashkilot turlari hali yaratilmagan. Yangi tur qo'shing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$organizationTypes" />
        </div>
    </div>
@endsection

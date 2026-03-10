@extends('layouts.dashboard')

@section('title', "Kelishuv yo'nalishlari")

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreement Directions</p>
                <h1 class="page-title">Kelishuv yo'nalishlari</h1>
                <p class="page-subtitle">Texnologiya, xavfsizlik va boshqa hamkorlik yo'nalishlarini boshqarish oynasi.</p>
            </div>

            @can('create agreement directions')
                <a class="btn btn--primary" href="{{ route('agreement-directions.create') }}">
                    <i class="material-icons" aria-hidden="true">playlist_add</i>
                    <span>Yangi yo'nalish</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('agreement-directions.index') }}">
            <label class="toolbar-search" aria-label="Kelishuv yo'nalishini qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom bo'yicha qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_alt</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('agreement-directions.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($agreementDirections->count())
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
                        @foreach ($agreementDirections as $agreementDirection)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $agreementDirection->name_uz }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $agreementDirection->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $agreementDirection->name_cryl }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit agreement directions')
                                            <a class="action-pill" href="{{ route('agreement-directions.edit', $agreementDirection) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete agreement directions')
                                            <form method="POST" action="{{ route('agreement-directions.destroy', $agreementDirection) }}" onsubmit="return confirm('Ushbu kelishuv yo\\'nalishini ochirishni tasdiqlaysizmi?');">
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
                    Kelishuv yo'nalishlari hali yaratilmagan. Yangi yo'nalish qo'shing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$agreementDirections" />
        </div>
    </div>
@endsection

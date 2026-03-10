@extends('layouts.dashboard')

@section('title', 'Kelishuv turlari')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreement Types</p>
                <h1 class="page-title">Kelishuv turlari</h1>
                <p class="page-subtitle">Memorandum, bitim, shartnoma va boshqa hujjat turlarini boshqarish oynasi.</p>
            </div>

            @can('create agreement types')
                <a class="btn btn--primary" href="{{ route('agreement-types.create') }}">
                    <i class="material-icons" aria-hidden="true">note_add</i>
                    <span>Yangi tur</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('agreement-types.index') }}">
            <label class="toolbar-search" aria-label="Kelishuv turi qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom bo'yicha qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_alt</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('agreement-types.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($agreementTypes->count())
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
                        @foreach ($agreementTypes as $agreementType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $agreementType->name_uz }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $agreementType->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $agreementType->name_cryl }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit agreement types')
                                            <a class="action-pill" href="{{ route('agreement-types.edit', $agreementType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete agreement types')
                                            <form method="POST" action="{{ route('agreement-types.destroy', $agreementType) }}" onsubmit="return confirm('Ushbu kelishuv turini ochirishni tasdiqlaysizmi?');">
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
                    Kelishuv turlari hali yaratilmagan. Yangi tur qo'shing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$agreementTypes" />
        </div>
    </div>
@endsection

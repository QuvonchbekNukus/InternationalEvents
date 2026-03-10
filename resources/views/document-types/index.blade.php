@extends('layouts.dashboard')

@section('title', 'Hujjat turlari')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Document Types</p>
                <h1 class="page-title">Hujjat turlari</h1>
                <p class="page-subtitle">Buyruq, memorandum, xat va boshqa hujjat turlarini boshqarish oynasi.</p>
            </div>

            @can('create document types')
                <a class="btn btn--primary" href="{{ route('document-types.create') }}">
                    <i class="material-icons" aria-hidden="true">description</i>
                    <span>Yangi tur</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('document-types.index') }}">
            <label class="toolbar-search" aria-label="Hujjat turi qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nom bo'yicha qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_alt</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('document-types.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($documentTypes->count())
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
                        @foreach ($documentTypes as $documentType)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $documentType->name_uz }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $documentType->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $documentType->name_cryl }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit document types')
                                            <a class="action-pill" href="{{ route('document-types.edit', $documentType) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete document types')
                                            <form method="POST" action="{{ route('document-types.destroy', $documentType) }}" onsubmit="return confirm('Ushbu hujjat turini o\\'chirishni tasdiqlaysizmi?');">
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
                    Hujjat turlari hali yaratilmagan. Yangi tur qo'shing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$documentTypes" />
        </div>
    </div>
@endsection

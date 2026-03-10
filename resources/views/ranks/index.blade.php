@extends('layouts.dashboard')

@section('title', 'Unvonlar')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Ranks</p>
                <h1 class="page-title">Unvonlar</h1>
                <p class="page-subtitle">Foydalanuvchilarga biriktiriladigan harbiy unvonlar ro'yxati.</p>
            </div>

            @can('create ranks')
                <a class="btn btn--primary" href="{{ route('ranks.create') }}">
                    <i class="material-icons" aria-hidden="true">military_tech</i>
                    <span>Yangi unvon</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('ranks.index') }}">
            <label class="toolbar-search" aria-label="Unvon qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Unvon nomini qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_alt</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('ranks.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($ranks->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Unvon</th>
                            <th>Ruscha</th>
                            <th>Xodimlar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ranks as $rank)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $rank->name_uz }}</span>
                                    <span class="row-subtitle">{{ $rank->name_cryl }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $rank->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ $rank->users_count }} ta</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit ranks')
                                            <a class="action-pill" href="{{ route('ranks.edit', $rank) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete ranks')
                                            <form method="POST" action="{{ route('ranks.destroy', $rank) }}" onsubmit="return confirm('Ushbu unvonni ochirishni tasdiqlaysizmi?');">
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
                    Unvonlar hali yaratilmagan. Yangi unvon qoshing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$ranks" />
        </div>
    </div>
@endsection

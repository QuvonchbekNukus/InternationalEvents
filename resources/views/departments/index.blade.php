@extends('layouts.dashboard')

@section('title', "Bo'limlar")

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Departments</p>
                <h1 class="page-title">Bo'limlar</h1>
                <p class="page-subtitle">Ichki tuzilma bo'limlari va ular bilan bog'langan foydalanuvchilar soni.</p>
            </div>

            @can('create departments')
                <a class="btn btn--primary" href="{{ route('departments.create') }}">
                    <i class="material-icons" aria-hidden="true">add_business</i>
                    <span>Yangi bo'lim</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('departments.index') }}">
            <label class="toolbar-search" aria-label="Bo'lim qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Bo'lim nomi yoki kodini qidiring">
            </label>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>Qidirish</span>
            </button>

            @if ($search !== '')
                <a class="btn btn--ghost" href="{{ route('departments.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($departments->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bo'lim</th>
                            <th>Kod</th>
                            <th>Tavsif</th>
                            <th>Xodimlar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $department)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $department->name_uz }}</span>
                                    <span class="row-subtitle">{{ $department->name_ru }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ $department->code ?: "Kod yo'q" }}</span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $department->description ?: "Tavsif kiritilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ $department->users_count }} ta</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit departments')
                                            <a class="action-pill" href="{{ route('departments.edit', $department) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete departments')
                                            <form method="POST" action="{{ route('departments.destroy', $department) }}" onsubmit="return confirm('Ushbu bolimni ochirishni tasdiqlaysizmi?');">
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
                    Bo'limlar ro'yxati bo'sh. Yangi bo'lim qo'shing yoki qidiruvni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$departments" />
        </div>
    </div>
@endsection

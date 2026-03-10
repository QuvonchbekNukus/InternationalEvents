@extends('layouts.dashboard')

@section('title', 'Foydalanuvchilar')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Users</p>
                <h1 class="page-title">Foydalanuvchilar</h1>
                <p class="page-subtitle">
                    Tizim foydalanuvchilari, rollar, bo'limlar va unvonlar bir sahifada boshqariladi.
                </p>
            </div>

            @can('create users')
                <a class="btn btn--primary" href="{{ route('users.create') }}">
                    <i class="material-icons" aria-hidden="true">person_add</i>
                    <span>Yangi foydalanuvchi</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('users.index') }}">
            <label class="toolbar-search" aria-label="Foydalanuvchi qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="F.I.SH, telefon yoki bo'lim bo'yicha qidirish">
            </label>

            <select class="toolbar-select" name="role" aria-label="Rol bo'yicha filter">
                <option value="">Barcha rollar</option>
                @foreach ($roles as $role)
                    <option value="{{ $role }}" @selected($filters['role'] === $role)>{{ \Illuminate\Support\Str::headline(str_replace('-', ' ', $role)) }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="department_id" aria-label="Bo'lim bo'yicha filter">
                <option value="">Barcha bo'limlar</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) $filters['department_id'] === (string) $department->id)>{{ $department->name_uz }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="Holat bo'yicha filter">
                <option value="">Barcha holatlar</option>
                <option value="active" @selected($filters['status'] === 'active')>Faol</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Nofaol</option>
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_alt</i>
                <span>Filtrlash</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('users.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($users->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Foydalanuvchi</th>
                            <th>Bo'lim</th>
                            <th>Unvon</th>
                            <th>Rol</th>
                            <th>Holat</th>
                            <th>Oxirgi kirish</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>
                                    <span class="row-title">{{ $user->full_name }}</span>
                                    <span class="row-subtitle">{{ $user->phone }}{{ $user->position_uz ? ' - '.$user->position_uz : '' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $user->department?->name_uz ?? "Biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $user->rank?->name_uz ?? '-' }}</span>
                                </td>
                                <td>
                                    <span class="badge">
                                        {{ $user->roles->first()?->name ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $user->roles->first()->name)) : "Rol yo'q" }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $user->is_active ? 'is-active' : 'is-muted' }}">
                                        {{ $user->is_active ? 'Faol' : 'Nofaol' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $user->last_login_at?->format('d.m.Y H:i') ?? "Kirish yo'q" }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit users')
                                            <a class="action-pill" href="{{ route('users.edit', $user) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete users')
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Ushbu foydalanuvchini ochirishni tasdiqlaysizmi?');">
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
                    Hozircha foydalanuvchilar topilmadi. Qidiruv filtrini tozalang yoki yangi foydalanuvchi qoshing.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$users" />
        </div>
    </div>
@endsection

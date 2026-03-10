@extends('layouts.dashboard')

@section('title', 'Davlatlar')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Countries</p>
                <h1 class="page-title">Hamkor davlatlar</h1>
                <p class="page-subtitle">Hamkorlik qilinayotgan yoki rejalashtirilgan davlatlar ma'lumotlari.</p>
            </div>

            @can('create countries')
                <a class="btn btn--primary" href="{{ route('countries.create') }}">
                    <i class="material-icons" aria-hidden="true">public</i>
                    <span>Yangi davlat</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('countries.index') }}">
            <label class="toolbar-search" aria-label="Davlat qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, ISO kodi yoki mintaqa bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="status" aria-label="Hamkorlik holati bo'yicha filter">
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
                <a class="btn btn--ghost" href="{{ route('countries.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($countries->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Davlat</th>
                            <th>Kodlar</th>
                            <th>Mintaqa</th>
                            <th>Koordinatalar</th>
                            <th>Holat</th>
                            <th>Fayllar</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($countries as $country)
                            @php
                                $statusClass = match ($country->cooperation_status) {
                                    'planned' => 'is-planned',
                                    'completed' => 'is-completed',
                                    default => 'is-active',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $country->display_name }}</span>
                                    <span class="row-subtitle">{{ $country->name_ru }}{{ $country->name_cryl ? ' / '.$country->name_cryl : '' }}</span>
                                </td>
                                <td>
                                    <span class="badge">{{ $country->iso2 ?: '--' }} / {{ $country->iso3 ?: '---' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $country->region_uz ?: ($country->region_ru ?: "Mintaqa yo'q") }}</span>
                                    @if ($country->notes)
                                        <span class="row-subtitle">{{ $country->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">
                                        {{ $country->latitude !== null && $country->longitude !== null ? number_format($country->latitude, 4).', '.number_format($country->longitude, 4) : "Koordinata yo'q" }}
                                    </span>
                                    <span class="row-subtitle">{{ $country->default_zoom !== null ? 'Zoom: '.number_format($country->default_zoom, 1) : "Zoom yo'q" }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$country->cooperation_status] ?? $country->cooperation_status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-subtitle">{{ $country->flag_path ?: "Bayroq yo'li kiritilmagan" }}</span>
                                    <span class="row-subtitle">{{ $country->boundary_geojson_path ?: "GeoJSON yo'li kiritilmagan" }}</span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit countries')
                                            <a class="action-pill" href="{{ route('countries.edit', $country) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete countries')
                                            <form method="POST" action="{{ route('countries.destroy', $country) }}" onsubmit="return confirm('Ushbu davlatni o\'chirishni tasdiqlaysizmi?');">
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
                    Davlatlar ro'yxati bo'sh. Yangi davlat qo'shing yoki filtrlarni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$countries" />
        </div>
    </div>
@endsection

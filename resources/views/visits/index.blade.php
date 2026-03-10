@extends('layouts.dashboard')

@section('title', 'Barcha tashriflar')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Visits</p>
                <h1 class="page-title">Barcha tashriflar</h1>
                <p class="page-subtitle">Tashriflar ro'yxati, yo'nalishlar, davlatlar va holatlar bo'yicha nazorat oynasi.</p>
            </div>

            @can('create visits')
                <a class="btn btn--primary" href="{{ route('visits.create') }}">
                    <i class="material-icons" aria-hidden="true">flight_takeoff</i>
                    <span>Yangi tashrif</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('visits.index') }}">
            <label class="toolbar-search" aria-label="Tashrif qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, davlat, tashkilot, shahar yoki maqsad bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="Davlat bo'yicha filter">
                <option value="">Barcha davlatlar</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->name_uz ?: $country->name_ru }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="visit_type_id" aria-label="Tashrif turi bo'yicha filter">
                <option value="">Barcha turlar</option>
                @foreach ($visitTypes as $visitType)
                    <option value="{{ $visitType->id }}" @selected((string) $filters['visit_type_id'] === (string) $visitType->id)>{{ $visitType->name_uz }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="direction" aria-label="Yo'nalish bo'yicha filter">
                <option value="">Barcha yo'nalishlar</option>
                @foreach ($directions as $directionValue => $directionLabel)
                    <option value="{{ $directionValue }}" @selected($filters['direction'] === $directionValue)>{{ $directionLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="Holat bo'yicha filter">
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
                <a class="btn btn--ghost" href="{{ route('visits.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($visits->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tashrif</th>
                            <th>Davlat / tashkilot</th>
                            <th>Turi / yo'nalishi</th>
                            <th>Manzil</th>
                            <th>Muddat</th>
                            <th>Javobgar</th>
                            <th>Holat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($visits as $visit)
                            @php
                                $statusClass = match ($visit->status) {
                                    'ongoing' => 'is-active',
                                    'completed' => 'is-completed',
                                    'planned' => 'is-planned',
                                    default => 'is-muted',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $visit->display_title }}</span>
                                    <span class="row-subtitle">{{ $visit->title_ru }}{{ $visit->title_cryl ? ' / '.$visit->title_cryl : '' }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->country?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $visit->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->visitType?->name_uz ?: "Tur biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $directions[$visit->direction] ?? "Yo'nalish tanlanmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->city ?: "Shahar yo'q" }}</span>
                                    <span class="row-subtitle">{{ $visit->address ?: "Manzil kiritilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->start_date?->format('d.m.Y') }}</span>
                                    <span class="row-subtitle">{{ $visit->end_date?->format('d.m.Y') ?: "Tugash sanasi yo'q" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $visit->responsibleUser?->full_name ?: "Javobgar biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $visit->responsibleDepartment?->name_uz ?: "Bo'lim biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$visit->status] ?? $visit->status }}
                                    </span>
                                    @if ($visit->purpose_uz || $visit->description)
                                        <span class="row-subtitle">{{ \Illuminate\Support\Str::limit($visit->purpose_uz ?: $visit->description, 90) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @canany(['edit visits', 'edit own visits'])
                                            <a class="action-pill" href="{{ route('visits.edit', $visit) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcanany

                                        @can('delete visits')
                                            <form method="POST" action="{{ route('visits.destroy', $visit) }}" onsubmit="return confirm('Ushbu tashrifni o\'chirishni tasdiqlaysizmi?');">
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
                    Tashriflar hali yaratilmagan. Yangi tashrif qo'shing yoki filtrlarni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$visits" />
        </div>
    </div>
@endsection

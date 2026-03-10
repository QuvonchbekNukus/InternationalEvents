@extends('layouts.dashboard')

@section('title', 'Barcha kelishuvlar')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreements</p>
                <h1 class="page-title">Barcha kelishuvlar</h1>
                <p class="page-subtitle">Davlat, tashkilot, yo'nalish va javobgarlar kesimida barcha kelishuvlar ro'yxati.</p>
            </div>

            @can('create agreements')
                <a class="btn btn--primary" href="{{ route('agreements.create') }}">
                    <i class="material-icons" aria-hidden="true">description</i>
                    <span>Yangi kelishuv</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('agreements.index') }}">
            <label class="toolbar-search" aria-label="Kelishuv qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Raqam, nom, davlat, tashkilot yoki javobgar bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="Davlat bo'yicha filter">
                <option value="">Barcha davlatlar</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->name_uz ?: $country->name_ru }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="agreement_type_id" aria-label="Kelishuv turi bo'yicha filter">
                <option value="">Barcha turlar</option>
                @foreach ($agreementTypes as $agreementType)
                    <option value="{{ $agreementType->id }}" @selected((string) $filters['agreement_type_id'] === (string) $agreementType->id)>{{ $agreementType->name_uz }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="agreement_direction_id" aria-label="Kelishuv yo'nalishi bo'yicha filter">
                <option value="">Barcha yo'nalishlar</option>
                @foreach ($agreementDirections as $agreementDirection)
                    <option value="{{ $agreementDirection->id }}" @selected((string) $filters['agreement_direction_id'] === (string) $agreementDirection->id)>{{ $agreementDirection->name_uz }}</option>
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
                <a class="btn btn--ghost" href="{{ route('agreements.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($agreements->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kelishuv</th>
                            <th>Davlat / tashkilot</th>
                            <th>Turi / yo'nalishi</th>
                            <th>Muddat</th>
                            <th>Javobgar</th>
                            <th>Holat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agreements as $agreement)
                            @php
                                $statusClass = match ($agreement->status) {
                                    'active' => 'is-active',
                                    'completed' => 'is-completed',
                                    'expired' => 'is-planned',
                                    default => 'is-muted',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $agreement->display_title }}</span>
                                    <span class="row-subtitle">
                                        {{ $agreement->agreement_number ?: "Raqam kiritilmagan" }}
                                        {{ ' - ' }}
                                        {{ $agreement->title_uz }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->country?->name_uz ?: ($agreement->country?->name_ru ?: '-') }}</span>
                                    <span class="row-subtitle">{{ $agreement->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->agreementType?->name_uz ?: "Tur biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $agreement->agreementDirection?->name_uz ?: "Yo'nalish biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->signed_date?->format('d.m.Y') ?: "Imzo sanasi yo'q" }}</span>
                                    <span class="row-subtitle">
                                        {{ $agreement->start_date?->format('d.m.Y') ?: '--' }}
                                        {{ ' - ' }}
                                        {{ $agreement->end_date?->format('d.m.Y') ?: '--' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->responsibleUser?->full_name ?: "Javobgar biriktirilmagan" }}</span>
                                    <span class="row-subtitle">{{ $agreement->responsibleDepartment?->name_uz ?: "Bo'lim biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$agreement->status] ?? $agreement->status }}
                                    </span>
                                    @if ($agreement->description)
                                        <span class="row-subtitle">{{ $agreement->description }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit agreements')
                                            <a class="action-pill" href="{{ route('agreements.edit', $agreement) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete agreements')
                                            <form method="POST" action="{{ route('agreements.destroy', $agreement) }}" onsubmit="return confirm('Ushbu kelishuvni ochirishni tasdiqlaysizmi?');">
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
                    Kelishuvlar hali yaratilmagan. Yangi kelishuv qo'shing yoki filtrlarni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$agreements" />
        </div>
    </div>
@endsection

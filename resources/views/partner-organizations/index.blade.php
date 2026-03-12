@extends('layouts.dashboard')

@section('title', __('ui.sidebar.partner_organizations'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_organizations')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.partner_organizations') }}</h1>
                <p class="page-subtitle">Davlatlar va tashkilot turlari kesimida hamkor subyektlar ro'yxati.</p>
            </div>

            @can('create partner organizations')
                <a class="btn btn--primary" href="{{ route('partner-organizations.create') }}">
                    <i class="material-icons" aria-hidden="true">business</i>
                    <span>Yangi tashkilot</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('partner-organizations.index') }}">
            <label class="toolbar-search" aria-label="Hamkor tashkilot qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Nom, qisqa nom, shahar yoki sayt bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="Davlat bo'yicha filter">
                <option value="">Barcha davlatlar</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="organization_type_id" aria-label="Tashkilot turi bo'yicha filter">
                <option value="">Barcha turlar</option>
                @foreach ($organizationTypes as $organizationType)
                    <option value="{{ $organizationType->id }}" @selected((string) $filters['organization_type_id'] === (string) $organizationType->id)>{{ $organizationType->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="Holat bo'yicha filter">
                <option value="">Barcha holatlar</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>Filtrlash</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('partner-organizations.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($partnerOrganizations->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tashkilot</th>
                            <th>Davlat</th>
                            <th>Turi</th>
                            <th>Manzil</th>
                            <th>Holat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partnerOrganizations as $partnerOrganization)
                            @php
                                $statusClass = match ($partnerOrganization->status) {
                                    'rejada' => 'is-planned',
                                    'tugallangan' => 'is-completed',
                                    default => 'is-active',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $partnerOrganization->display_name }}</span>
                                    <span class="row-subtitle">
                                        {{ $partnerOrganization->short_name ?: "Qisqa nom yo'q" }}
                                        @if ($partnerOrganization->website)
                                            {{ ' - '.$partnerOrganization->website }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerOrganization->country?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $partnerOrganization->country?->iso2 ?: "ISO2 yo'q" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerOrganization->organizationType?->display_name ?: "Tur biriktirilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerOrganization->city ?: "Shahar yo'q" }}</span>
                                    <span class="row-subtitle">{{ $partnerOrganization->address ?: "Manzil kiritilmagan" }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$partnerOrganization->status] ?? $partnerOrganization->status }}
                                    </span>
                                    @if ($partnerOrganization->notes)
                                        <span class="row-subtitle">{{ $partnerOrganization->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit partner organizations')
                                            <a class="action-pill" href="{{ route('partner-organizations.edit', $partnerOrganization) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete partner organizations')
                                            <form method="POST" action="{{ route('partner-organizations.destroy', $partnerOrganization) }}" onsubmit="return confirm('Ushbu hamkor tashkilotni ochirishni tasdiqlaysizmi?');">
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
                    Hamkor tashkilotlar hali yaratilmagan. Yangi tashkilot qo'shing yoki filtrlarni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$partnerOrganizations" />
        </div>
    </div>
@endsection

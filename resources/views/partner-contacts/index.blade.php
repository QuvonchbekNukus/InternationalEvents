@extends('layouts.dashboard')

@section('title', __('ui.sidebar.partner_contacts'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_contacts')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.partner_contacts') }}</h1>
                <p class="page-subtitle">Hamkor tashkilotlarga biriktirilgan asosiy va yordamchi kontaktlar, aloqa, tug'ilgan sana va biriktirilgan fayllar ma'lumotlari.</p>
            </div>

            @can('create partner contacts')
                <a class="btn btn--primary" href="{{ route('partner-contacts.create') }}">
                    <i class="material-icons" aria-hidden="true">perm_contact_calendar</i>
                    <span>Yangi kontakt</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('partner-contacts.index') }}">
            <label class="toolbar-search" aria-label="Hamkor kontakt qidirish">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="F.I.Sh, tug'ilgan sana, lavozim yoki tashkilot bo'yicha qidiring">
            </label>

            <select class="toolbar-select" name="partner_organization_id" aria-label="Hamkor tashkilot bo'yicha filter">
                <option value="">Barcha tashkilotlar</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option value="{{ $partnerOrganization->id }}" @selected((string) $filters['partner_organization_id'] === (string) $partnerOrganization->id)>
                        {{ $partnerOrganization->display_name }}
                    </option>
                @endforeach
            </select>

            <select class="toolbar-select" name="primary" aria-label="Asosiy kontakt bo'yicha filter">
                <option value="">Barchasi</option>
                <option value="1" @selected($filters['primary'] === '1')>Asosiy</option>
                <option value="0" @selected($filters['primary'] === '0')>Oddiy</option>
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>Filtrlash</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('partner-contacts.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>Tozalash</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($partnerContacts->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kontakt</th>
                            <th>Tashkilot</th>
                            <th>Tug'ilgan sana</th>
                            <th>Lavozim</th>
                            <th>Aloqa</th>
                            <th>Holat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partnerContacts as $partnerContact)
                            <tr>
                                <td>
                                    <span class="row-title">
                                        <a class="row-title-link" href="{{ route('partner-contacts.show', $partnerContact) }}">{{ $partnerContact->display_name }}</a>
                                    </span>
                                    <span class="row-subtitle">{{ $partnerContact->full_name_ru }}</span>
                                    @if ($partnerContact->photoDocument || $partnerContact->cvDocument)
                                        <span class="row-subtitle">
                                            @if ($partnerContact->photoDocument?->file_url)
                                                <a href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">Foto</a>
                                            @endif
                                            @if ($partnerContact->photoDocument?->file_url && $partnerContact->cvDocument?->file_url)
                                                /
                                            @endif
                                            @if ($partnerContact->cvDocument?->file_url)
                                                <a href="{{ $partnerContact->cvDocument->file_url }}" target="_blank" rel="noopener">CV</a>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->partnerOrganization?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">
                                        {{ $partnerContact->partnerOrganization?->country?->display_name ?: "Davlat yo'q" }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->birthday?->format('d.m.Y') ?: "Kiritilmagan" }}</span>
                                    @if ($partnerContact->birthday)
                                        <span class="row-subtitle">{{ $partnerContact->birthday->age }} yosh</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->display_position ?: "Lavozim kiritilmagan" }}</span>
                                    @if ($partnerContact->position_ru)
                                        <span class="row-subtitle">{{ $partnerContact->position_ru }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->email ?: "Email yo'q" }}</span>
                                    <span class="row-subtitle">{{ $partnerContact->phone ?: "Telefon yo'q" }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $partnerContact->is_primary ? 'is-active' : 'is-muted' }}">
                                        {{ $partnerContact->is_primary ? 'Asosiy' : 'Oddiy' }}
                                    </span>
                                    @if ($partnerContact->description)
                                        <span class="row-subtitle">{{ $partnerContact->description }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit partner contacts')
                                            <a class="action-pill" href="{{ route('partner-contacts.edit', $partnerContact) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>Tahrirlash</span>
                                            </a>
                                        @endcan

                                        @can('delete partner contacts')
                                            <form method="POST" action="{{ route('partner-contacts.destroy', $partnerContact) }}" onsubmit="return confirm('Ushbu hamkor kontaktni ochirishni tasdiqlaysizmi?');">
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
                    Hamkor kontaktlar hali yaratilmagan. Yangi kontakt qo'shing yoki filtrlarni tozalang.
                </div>
            @endif

            <x-dashboard-pagination :paginator="$partnerContacts" />
        </div>
    </div>
@endsection

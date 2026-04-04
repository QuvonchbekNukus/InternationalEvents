@extends('layouts.dashboard')

@section('title', $country->display_name)

@section('content')
    @php
        $statusClass = match ($country->cooperation_status) {
            'rejada' => 'is-planned',
            'tugatilgan' => 'is-completed',
            default => 'is-active',
        };
        $countryHasCoordinates = $country->latitude !== null && $country->longitude !== null;
        $countryMapZoom = (int) ($country->default_zoom ?? \App\Models\Country::DEFAULT_MAP_ZOOM);
        $countryMapDescription = implode(' | ', array_filter([
            $country->display_region,
            $statuses[$country->cooperation_status] ?? $country->cooperation_status,
            $countryHasCoordinates
                ? number_format($country->latitude, 4).', '.number_format($country->longitude, 4)
                : null,
        ]));
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.countries')]) }}</p>
                <h1 class="page-title">{{ $country->display_name }}</h1>
                <p class="page-subtitle">Davlat bo'yicha asosiy ma'lumotlar va unga bog'langan tashkilot, kelishuv, tadbir, tashrif hamda hujjatlar bir sahifada jamlandi.</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('countries.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>{{ __('ui.common.actions.back_to_list') }}</span>
                </a>

                @can('edit countries')
                    <a class="btn btn--primary" href="{{ route('countries.edit', $country) }}">
                        <i class="material-icons" aria-hidden="true">edit</i>
                        <span>{{ __('ui.common.actions.edit') }}</span>
                    </a>
                @endcan
            </div>
        </div>

        <div class="detail-grid">
            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Asosiy</p>
                        <h2 class="section-title">Davlat kartasi</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Hamkorlik holati</span>
                        <span class="status-pill {{ $statusClass }}">
                            {{ $statuses[$country->cooperation_status] ?? $country->cooperation_status }}
                        </span>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Hudud</span>
                        <strong>{{ $country->display_region ?: "Hudud ko'rsatilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Kodlar</span>
                        <strong>{{ $country->iso2 ?: '--' }} / {{ $country->iso3 ?: '---' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Koordinatalar</span>
                        <strong>
                            {{ $country->latitude !== null && $country->longitude !== null ? number_format($country->latitude, 4).', '.number_format($country->longitude, 4) : "Koordinatalar kiritilmagan" }}
                        </strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Qo'shimcha</p>
                        <h2 class="section-title">Fayl va izohlar</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Bayroq</span>
                        @if ($country->has_flag_file)
                            <div class="detail-media">
                                <img
                                    class="detail-media__thumb"
                                    src="{{ asset($country->flag_asset_path) }}"
                                    alt="{{ $country->display_name }}"
                                >
                                <div class="detail-media__body">
                                    <strong>{{ $country->flag_asset_path }}</strong>
                                    <span>ISO2 kodi bo'yicha topilgan bayroq fayli.</span>
                                </div>
                            </div>
                        @else
                            <strong>Bayroq fayli topilmadi</strong>
                        @endif
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">GeoJSON</span>
                        <strong>{{ $country->boundary_geojson_path ?: "Chegara fayli biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Izoh</span>
                        <p class="detail-note">{{ $country->notes ?: "Qo'shimcha izoh kiritilmagan." }}</p>
                    </article>
                </div>
            </section>
        </div>

        <div class="stats-grid">
            @if ($relatedAccess['partner_organizations'])
                <article class="stat-card">
                    <div class="stat-card__head">
                        <span class="stat-icon">
                            <i class="material-icons" aria-hidden="true">business</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $partnerOrganizations->count() }}</strong>
                    <h2 class="stat-title">Hamkor tashkilotlar</h2>
                    <p class="stat-description">Davlatga biriktirilgan tashkilotlar soni.</p>
                </article>
            @endif

            @if ($relatedAccess['agreements'])
                <article class="stat-card">
                    <div class="stat-card__head">
                        <span class="stat-icon">
                            <i class="material-icons" aria-hidden="true">description</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $agreements->count() }}</strong>
                    <h2 class="stat-title">Kelishuvlar</h2>
                    <p class="stat-description">Ushbu davlat bo'yicha ko'rishga ruxsat etilgan kelishuvlar.</p>
                </article>
            @endif

            @if ($relatedAccess['events'])
                <article class="stat-card">
                    <div class="stat-card__head">
                        <span class="stat-icon">
                            <i class="material-icons" aria-hidden="true">event</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $events->count() }}</strong>
                    <h2 class="stat-title">Tadbirlar</h2>
                    <p class="stat-description">Davlat bilan bog'langan tadbirlar soni.</p>
                </article>
            @endif

            @if ($relatedAccess['visits'])
                <article class="stat-card">
                    <div class="stat-card__head">
                        <span class="stat-icon">
                            <i class="material-icons" aria-hidden="true">flight_takeoff</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $visits->count() }}</strong>
                    <h2 class="stat-title">Tashriflar</h2>
                    <p class="stat-description">Davlat kesimidagi tashriflar soni.</p>
                </article>
            @endif

            @if ($relatedAccess['documents'])
                <article class="stat-card">
                    <div class="stat-card__head">
                        <span class="stat-icon">
                            <i class="material-icons" aria-hidden="true">folder</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $documents->count() }}</strong>
                    <h2 class="stat-title">Hujjatlar</h2>
                    <p class="stat-description">Davlatga tegishli yoki u bilan bog'langan fayllar.</p>
                </article>
            @endif
        </div>

        @if ($relatedAccess['partner_organizations'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Hamkor tashkilotlar</h2>
                    </div>
                </div>

                @if ($partnerOrganizations->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($partnerOrganizations as $partnerOrganization)
                            <article class="stack-list__item">
                                <strong>
                                    <a class="row-title-link" href="{{ route('partner-organizations.show', $partnerOrganization) }}">
                                        {{ $partnerOrganization->display_name }}
                                    </a>
                                </strong>
                                <span>
                                    {{ $partnerOrganization->organizationType?->display_name ?: "Turi ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ \App\Models\PartnerOrganization::STATUS_LABELS[$partnerOrganization->status] ?? $partnerOrganization->status }}
                                </span>
                                <span>
                                    {{ $partnerOrganization->city ?: "Shahar ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ $partnerOrganization->partner_contacts_count }} ta kontakt
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu davlatga hali hamkor tashkilot biriktirilmagan.</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['agreements'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Kelishuvlar</h2>
                    </div>
                </div>

                @if ($agreements->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($agreements as $agreement)
                            <article class="stack-list__item">
                                <strong>
                                    <a class="row-title-link" href="{{ route('agreements.show', $agreement) }}">
                                        {{ $agreement->display_title }}
                                    </a>
                                </strong>
                                <span>
                                    {{ $agreement->agreement_number ?: "Raqam biriktirilmagan" }}
                                    {{ ' | ' }}
                                    {{ $agreement->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}
                                </span>
                                <span>
                                    {{ $agreement->agreementType?->display_name ?: "Tur ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ $agreement->signed_date?->format('d.m.Y') ?: "Imzolangan sana yo'q" }}
                                    {{ ' | ' }}
                                    {{ \App\Models\Agreement::STATUS_LABELS[$agreement->status] ?? $agreement->status }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu davlat bo'yicha ko'rinadigan kelishuv topilmadi.</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['events'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Tadbirlar</h2>
                    </div>
                </div>

                @if ($events->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($events as $event)
                            <article class="stack-list__item">
                                <strong>
                                    <a class="row-title-link" href="{{ route('events.show', $event) }}">
                                        {{ $event->display_title }}
                                    </a>
                                </strong>
                                <span>
                                    {{ $event->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}
                                    @if ($event->agreement)
                                        {{ ' | Kelishuv: '.$event->agreement->display_title }}
                                    @endif
                                </span>
                                <span>
                                    {{ $event->eventType?->display_name ?: "Tur ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ $event->start_datetime?->format('d.m.Y H:i') ?: "Boshlanish vaqti yo'q" }}
                                    {{ ' | ' }}
                                    {{ \App\Models\Event::STATUS_LABELS[$event->status] ?? $event->status }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu davlat bilan bog'langan tadbir topilmadi.</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['visits'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Tashriflar</h2>
                    </div>
                </div>

                @if ($visits->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($visits as $visit)
                            <article class="stack-list__item">
                                <strong>
                                    <a class="row-title-link" href="{{ route('visits.show', $visit) }}">
                                        {{ $visit->display_title }}
                                    </a>
                                </strong>
                                <span>
                                    {{ $visit->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}
                                    {{ ' | ' }}
                                    {{ $visit->visitType?->display_name ?: "Tur ko'rsatilmagan" }}
                                </span>
                                <span>
                                    {{ $visit->start_date?->format('d.m.Y') ?: "Boshlanish sanasi yo'q" }}
                                    {{ ' | ' }}
                                    {{ \App\Models\Visit::DIRECTION_LABELS[$visit->direction] ?? "Yo'nalish ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ \App\Models\Visit::STATUS_LABELS[$visit->status] ?? $visit->status }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu davlat bilan bog'langan tashrif topilmadi.</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['documents'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Hujjatlar</h2>
                    </div>
                </div>

                @if ($documents->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($documents as $document)
                            <article class="stack-list__item">
                                <strong>{{ $document->display_title }}</strong>
                                <span>
                                    {{ $document->documentType?->display_name ?: "Tur biriktirilmagan" }}
                                    {{ ' | ' }}
                                    {{ strtoupper($document->file_ext ?: 'fayl') }}
                                    @if ($document->file_size_human)
                                        {{ ' | '.$document->file_size_human }}
                                    @endif
                                </span>
                                <span>
                                    {{ $document->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}
                                    @if ($document->agreement)
                                        {{ ' | Kelishuv: '.$document->agreement->display_title }}
                                    @endif
                                    @if ($document->visit)
                                        {{ ' | Tashrif: '.$document->visit->display_title }}
                                    @endif
                                    @if ($document->event)
                                        {{ ' | Tadbir: '.$document->event->display_title }}
                                    @endif
                                </span>
                                <div class="detail-actions-inline">
                                    <a class="action-pill" href="{{ route('documents.download', $document) }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>Faylni olish</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu davlat bo'yicha ko'rinadigan hujjat topilmadi.</p>
                @endif
            </section>
        @endif

        @if ($countryHasCoordinates)
            <x-leaflet-map
                eyebrow="Geolokatsiya"
                title="{{ $country->display_name }} xaritada"
                subtitle="Davlat uchun kiritilgan koordinatalar xaritada marker bilan ko'rsatildi."
                :height="400"
                :center="[$country->latitude, $country->longitude]"
                :zoom="$countryMapZoom"
                :markers="[[
                    'lat' => $country->latitude,
                    'lng' => $country->longitude,
                    'title' => $country->display_name,
                    'description' => $countryMapDescription,
                    'openPopup' => true,
                ]]"
                :chips="array_values(array_filter([
                    $country->iso2 ? 'ISO2: '.$country->iso2 : null,
                    $country->iso3 ? 'ISO3: '.$country->iso3 : null,
                    $country->display_region,
                ]))"
            />
        @else
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Geolokatsiya</p>
                        <h2 class="section-title">Davlat xaritada</h2>
                    </div>
                </div>

                <p class="detail-empty">Davlat uchun latitude va longitude kiritilmagani sababli xarita ko'rsatilmadi.</p>
            </section>
        @endif
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', $country->display_name)

@section('content')
    @php
        $statusClass = match ($country->cooperation_status) {
            'rejada' => 'is-planned',
            'tugatilgan' => 'is-completed',
            default => 'is-active',
        };
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.countries')]) }}</p>
                <h1 class="page-title">{{ $country->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.countries.show.subtitle') }}</p>
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
                        <p class="eyebrow">{{ __('ui.pages.countries.show.eyebrows.main') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.countries.show.sections.country_card') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.countries.show.labels.cooperation_status') }}</span>
                        <span class="status-pill {{ $statusClass }}">
                            {{ $statuses[$country->cooperation_status] ?? $country->cooperation_status }}
                        </span>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.countries.show.labels.region') }}</span>
                        <strong>{{ $country->display_region ?: __('ui.pages.countries.show.values.region_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.countries.show.labels.codes') }}</span>
                        <strong>{{ $country->iso2 ?: '--' }} / {{ $country->iso3 ?: '---' }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.pages.countries.show.labels.partnership_history') }}</span>
                        @if ($country->partnershipHistoryDocument)
                            <div class="detail-actions-inline">
                                <strong>{{ $country->partnershipHistoryDocument->file_name }}</strong>
                                @if (auth()->user()?->can('view documents') || auth()->user()?->can('view own documents'))
                                    <a class="action-pill" href="{{ route('documents.download', $country->partnershipHistoryDocument) }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>{{ __('ui.common.actions.download_file') }}</span>
                                    </a>
                                @endif
                            </div>
                        @else
                            <p class="detail-note">{{ __('ui.pages.countries.show.values.partnership_history_missing') }}</p>
                        @endif
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.countries.show.eyebrows.additional') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.countries.show.sections.files_notes') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.countries.show.labels.flag') }}</span>
                        @if ($country->has_flag_file)
                            <div class="detail-media">
                                <img
                                    class="detail-media__thumb"
                                    src="{{ asset($country->flag_asset_path) }}"
                                    alt="{{ $country->display_name }}"
                                >
                                <div class="detail-media__body">
                                    <strong>{{ $country->flag_asset_path }}</strong>
                                    <span>{{ __('ui.pages.countries.show.values.flag_found_by_iso2') }}</span>
                                </div>
                            </div>
                        @else
                            <strong>{{ __('ui.pages.countries.show.values.flag_missing') }}</strong>
                        @endif
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">GeoJSON</span>
                        <strong>{{ $country->boundary_geojson_path ?: __('ui.pages.countries.show.values.geojson_missing') }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.pages.countries.show.labels.notes') }}</span>
                        <p class="detail-note">{{ $country->notes ?: __('ui.pages.countries.show.values.notes_missing') }}</p>
                    </article>
                </div>
            </section>
        </div>

        <div class="stats-grid dashboard-stats-grid">
            @if ($relatedAccess['partner_organizations'])
                <article class="stat-card dashboard-stat-card dashboard-stat-card--azure">
                    <div class="stat-card__head">
                        <span class="stat-icon app-icon-box app-icon-box--lg">
                            <i class="material-icons app-icon app-icon--lg" aria-hidden="true">business</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $partnerOrganizations->count() }}</strong>
                    <h2 class="stat-title">{{ __('ui.pages.countries.show.stats.partner_organizations_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.countries.show.stats.partner_organizations_description') }}</p>
                </article>
            @endif

            @if ($relatedAccess['agreements'])
                <article class="stat-card dashboard-stat-card dashboard-stat-card--emerald">
                    <div class="stat-card__head">
                        <span class="stat-icon app-icon-box app-icon-box--lg">
                            <i class="material-icons app-icon app-icon--lg" aria-hidden="true">description</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $agreements->count() }}</strong>
                    <h2 class="stat-title">{{ __('ui.pages.countries.show.stats.agreements_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.countries.show.stats.agreements_description') }}</p>
                </article>
            @endif

            @if ($relatedAccess['events'])
                <article class="stat-card dashboard-stat-card dashboard-stat-card--amber">
                    <div class="stat-card__head">
                        <span class="stat-icon app-icon-box app-icon-box--lg">
                            <i class="material-icons app-icon app-icon--lg" aria-hidden="true">event</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $events->count() }}</strong>
                    <h2 class="stat-title">{{ __('ui.pages.countries.show.stats.events_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.countries.show.stats.events_description') }}</p>
                </article>
            @endif

            @if ($relatedAccess['visits'])
                <article class="stat-card dashboard-stat-card dashboard-stat-card--cyan">
                    <div class="stat-card__head">
                        <span class="stat-icon app-icon-box app-icon-box--lg">
                            <i class="material-icons app-icon app-icon--lg" aria-hidden="true">flight_takeoff</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $visits->count() }}</strong>
                    <h2 class="stat-title">{{ __('ui.pages.countries.show.stats.visits_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.countries.show.stats.visits_description') }}</p>
                </article>
            @endif

            @if ($relatedAccess['documents'])
                <article class="stat-card dashboard-stat-card dashboard-stat-card--indigo">
                    <div class="stat-card__head">
                        <span class="stat-icon app-icon-box app-icon-box--lg">
                            <i class="material-icons app-icon app-icon--lg" aria-hidden="true">folder</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $documents->count() }}</strong>
                    <h2 class="stat-title">{{ __('ui.pages.countries.show.stats.documents_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.countries.show.stats.documents_description') }}</p>
                </article>
            @endif
        </div>

        @if ($relatedAccess['partner_organizations'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.countries.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.countries.show.related.partner_organizations_title') }}</h2>
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
                                    {{ $partnerOrganization->organizationType?->display_name ?: __('ui.pages.countries.show.values.type_missing') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\PartnerOrganization::STATUS_TRANSLATION_KEY.'.'.$partnerOrganization->status) }}
                                </span>
                                <span>
                                    {{ $partnerOrganization->city ?: __('ui.pages.countries.show.values.city_missing') }}
                                    {{ ' | ' }}
                                    {{ __('ui.pages.countries.show.values.contacts_count', ['count' => $partnerOrganization->partner_contacts_count]) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.countries.show.related.partner_organizations_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['agreements'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.countries.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.countries.show.related.agreements_title') }}</h2>
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
                                    {{ $agreement->agreement_number ?: __('ui.pages.countries.show.values.number_missing') }}
                                    {{ ' | ' }}
                                    {{ $agreement->partnerOrganization?->display_name ?: __('ui.pages.countries.show.values.organization_missing') }}
                                </span>
                                <span>
                                    {{ $agreement->agreementType?->display_name ?: __('ui.pages.countries.show.values.type_missing') }}
                                    {{ ' | ' }}
                                    {{ $agreement->signed_date?->format('d.m.Y') ?: __('ui.pages.countries.show.values.signed_date_missing') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Agreement::STATUS_TRANSLATION_KEY.'.'.$agreement->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.countries.show.related.agreements_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['events'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.countries.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.countries.show.related.events_title') }}</h2>
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
                                    {{ $event->partnerOrganization?->display_name ?: __('ui.pages.countries.show.values.organization_missing') }}
                                    @if ($event->agreement)
                                        {{ ' | '.__('ui.pages.countries.show.values.related_agreement').': '.$event->agreement->display_title }}
                                    @endif
                                </span>
                                <span>
                                    {{ $event->eventType?->display_name ?: __('ui.pages.countries.show.values.type_missing') }}
                                    {{ ' | ' }}
                                    {{ $event->start_datetime?->format('d.m.Y H:i') ?: __('ui.pages.countries.show.values.start_datetime_missing') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Event::STATUS_TRANSLATION_KEY.'.'.$event->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.countries.show.related.events_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['visits'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.countries.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.countries.show.related.visits_title') }}</h2>
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
                                    {{ $visit->partnerOrganization?->display_name ?: __('ui.pages.countries.show.values.organization_missing') }}
                                    {{ ' | ' }}
                                    {{ $visit->visitType?->display_name ?: __('ui.pages.countries.show.values.type_missing') }}
                                </span>
                                <span>
                                    {{ $visit->start_date?->format('d.m.Y') ?: __('ui.pages.countries.show.values.start_date_missing') }}
                                    {{ ' | ' }}
                                    {{ $visit->direction ? __(\App\Models\Visit::DIRECTION_TRANSLATION_KEY.'.'.$visit->direction) : __('ui.common.values.unknown') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Visit::STATUS_TRANSLATION_KEY.'.'.$visit->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.countries.show.related.visits_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['documents'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.countries.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.countries.show.related.documents_title') }}</h2>
                    </div>
                </div>

                @if ($documents->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($documents as $document)
                            <article class="stack-list__item">
                                <strong>{{ $document->display_title }}</strong>
                                <span>
                                    {{ $document->documentType?->display_name ?: __('ui.pages.countries.show.values.type_unassigned') }}
                                    {{ ' | ' }}
                                    {{ strtoupper($document->file_ext ?: __('ui.pages.countries.show.values.file')) }}
                                    @if ($document->file_size_human)
                                        {{ ' | '.$document->file_size_human }}
                                    @endif
                                </span>
                                <span>
                                    {{ $document->partnerOrganization?->display_name ?: __('ui.pages.countries.show.values.organization_missing') }}
                                    @if ($document->agreement)
                                        {{ ' | '.__('ui.pages.countries.show.values.related_agreement').': '.$document->agreement->display_title }}
                                    @endif
                                    @if ($document->visit)
                                        {{ ' | '.__('ui.pages.countries.show.values.related_visit').': '.$document->visit->display_title }}
                                    @endif
                                    @if ($document->event)
                                        {{ ' | '.__('ui.pages.countries.show.values.related_event').': '.$document->event->display_title }}
                                    @endif
                                </span>
                                <div class="detail-actions-inline">
                                    <a class="action-pill" href="{{ route('documents.download', $document) }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>{{ __('ui.common.actions.download_file') }}</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.countries.show.related.documents_empty') }}</p>
                @endif
            </section>
        @endif

    </div>
@endsection

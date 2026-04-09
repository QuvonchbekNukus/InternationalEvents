@extends('layouts.dashboard')

@section('title', $partnerOrganization->display_name)

@section('content')
    @php
        $statusClass = match ($partnerOrganization->status) {
            'rejada' => 'is-planned',
            'tugallangan' => 'is-completed',
            default => 'is-active',
        };
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_organizations')]) }}</p>
                <h1 class="page-title">{{ $partnerOrganization->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_organizations.show.subtitle') }}</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('partner-organizations.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>{{ __('ui.common.actions.back_to_list') }}</span>
                </a>

                @can('edit partner organizations')
                    <a class="btn btn--primary" href="{{ route('partner-organizations.edit', $partnerOrganization) }}">
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
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.main') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.sections.organization_info') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.country') }}</span>
                        <strong>
                            @if ($partnerOrganization->country && auth()->user()?->can('view countries'))
                                <a class="row-title-link" href="{{ route('countries.show', $partnerOrganization->country) }}">
                                    {{ $partnerOrganization->country->display_name }}
                                </a>
                            @else
                                {{ $partnerOrganization->country?->display_name ?: __('ui.pages.partner_organizations.show.values.country_missing') }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.organization_type') }}</span>
                        <strong>{{ $partnerOrganization->organizationType?->display_name ?: __('ui.pages.partner_organizations.show.values.type_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.short_name') }}</span>
                        <strong>{{ $partnerOrganization->short_name ?: __('ui.pages.partner_organizations.show.values.short_name_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.info_file') }}</span>
                        <strong>{{ $partnerOrganization->organizationInfoDocument?->file_name ?: __('ui.pages.partner_organizations.show.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.status') }}</span>
                        <span class="status-pill {{ $statusClass }}">
                            {{ $statuses[$partnerOrganization->status] ?? $partnerOrganization->status }}
                        </span>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.contact') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.sections.location_notes') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.city') }}</span>
                        <strong>{{ $partnerOrganization->city ?: __('ui.pages.partner_organizations.show.values.city_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.address') }}</span>
                        <strong>{{ $partnerOrganization->address ?: __('ui.pages.partner_organizations.show.values.address_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.website') }}</span>
                        <strong>
                            @if ($partnerOrganization->website_url)
                                <a class="row-title-link" href="{{ $partnerOrganization->website_url }}" target="_blank" rel="noreferrer">
                                    {{ $partnerOrganization->website }}
                                </a>
                            @else
                                {{ __('ui.pages.partner_organizations.show.values.website_missing') }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.notes') }}</span>
                        <p class="detail-note">{{ $partnerOrganization->notes ?: __('ui.pages.partner_organizations.show.values.notes_missing') }}</p>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.pages.partner_organizations.show.labels.info_file_actions') }}</span>
                        @if ($partnerOrganization->organizationInfoDocument?->file_url)
                            <strong>{{ $partnerOrganization->organizationInfoDocument->display_title }}</strong>
                            <div class="detail-actions-inline">
                                <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" target="_blank" rel="noopener">
                                    <i class="material-icons" aria-hidden="true">open_in_new</i>
                                    <span>{{ __('ui.common.actions.open') }}</span>
                                </a>
                                <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                                    <i class="material-icons" aria-hidden="true">file_download</i>
                                    <span>{{ __('ui.common.actions.download_file') }}</span>
                                </a>
                            </div>
                        @else
                            <p class="detail-empty">{{ __('ui.pages.partner_organizations.show.values.info_file_missing') }}</p>
                        @endif
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.partnership') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.sections.partnership_history') }}</h2>
                    </div>
                </div>

                @if ($partnerOrganization->partnershipHistoryDocument)
                    <div class="detail-actions-inline">
                        <strong>{{ $partnerOrganization->partnershipHistoryDocument->file_name }}</strong>
                        @if (auth()->user()?->can('view documents') || auth()->user()?->can('view own documents'))
                            <a class="action-pill" href="{{ route('documents.download', $partnerOrganization->partnershipHistoryDocument) }}">
                                <i class="material-icons" aria-hidden="true">file_download</i>
                                <span>{{ __('ui.common.actions.download_file') }}</span>
                            </a>
                        @endif
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.partner_organizations.show.values.partnership_history_missing') }}</p>
                @endif
            </section>
        </div>

        <div class="stats-grid">
            @if ($relatedAccess['partner_contacts'])
                <article class="stat-card">
                    <div class="stat-card__head">
                        <span class="stat-icon">
                            <i class="material-icons" aria-hidden="true">perm_contact_calendar</i>
                        </span>
                    </div>
                    <strong class="stat-value">{{ $partnerContacts->count() }}</strong>
                    <h2 class="stat-title">{{ __('ui.pages.partner_organizations.show.stats.contacts_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.partner_organizations.show.stats.contacts_description') }}</p>
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
                    <h2 class="stat-title">{{ __('ui.pages.partner_organizations.show.stats.agreements_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.partner_organizations.show.stats.agreements_description') }}</p>
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
                    <h2 class="stat-title">{{ __('ui.pages.partner_organizations.show.stats.events_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.partner_organizations.show.stats.events_description') }}</p>
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
                    <h2 class="stat-title">{{ __('ui.pages.partner_organizations.show.stats.visits_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.partner_organizations.show.stats.visits_description') }}</p>
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
                    <h2 class="stat-title">{{ __('ui.pages.partner_organizations.show.stats.documents_title') }}</h2>
                    <p class="stat-description">{{ __('ui.pages.partner_organizations.show.stats.documents_description') }}</p>
                </article>
            @endif
        </div>

        @if ($relatedAccess['partner_contacts'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.related.contacts_title') }}</h2>
                    </div>
                </div>

                @if ($partnerContacts->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($partnerContacts as $partnerContact)
                            <article class="stack-list__item">
                                <strong>
                                    <a class="row-title-link" href="{{ route('partner-contacts.show', $partnerContact) }}">
                                        {{ $partnerContact->display_name }}
                                    </a>
                                </strong>
                                <span>
                                    {{ $partnerContact->display_position ?: __('ui.pages.partner_organizations.show.values.position_missing') }}
                                    {{ ' | ' }}
                                    {{ $partnerContact->birthday?->format('d.m.Y') ?: __('ui.pages.partner_organizations.show.values.birthday_missing') }}
                                </span>
                                <span>
                                    {{ $partnerContact->email ?: __('ui.pages.partner_organizations.show.values.email_missing') }}
                                    {{ ' | ' }}
                                    {{ $partnerContact->phone ?: __('ui.pages.partner_organizations.show.values.phone_missing') }}
                                </span>
                                <div class="detail-actions-inline">
                                    @if ($partnerContact->is_primary)
                                        <span class="status-pill is-active">{{ __('ui.pages.partner_organizations.show.values.primary_contact') }}</span>
                                    @endif

                                    @if ($partnerContact->photoDocument?->file_url)
                                        <a class="action-pill" href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">
                                            <i class="material-icons" aria-hidden="true">image</i>
                                            <span>{{ __('ui.pages.partner_organizations.show.values.photo') }}</span>
                                        </a>
                                    @endif

                                    @if ($partnerContact->cvDocument?->file_url)
                                        <a class="action-pill" href="{{ $partnerContact->cvDocument->file_url }}" target="_blank" rel="noopener">
                                            <i class="material-icons" aria-hidden="true">description</i>
                                            <span>{{ __('ui.pages.partner_organizations.show.values.cv') }}</span>
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.partner_organizations.show.related.contacts_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['agreements'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.related.agreements_title') }}</h2>
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
                                    {{ $agreement->agreementType?->display_name ?: __('ui.pages.partner_organizations.show.values.type_missing') }}
                                    {{ ' | ' }}
                                    {{ $agreement->agreementDirection?->display_name ?: __('ui.pages.partner_organizations.show.values.direction_missing') }}
                                </span>
                                <span>
                                    {{ $agreement->signed_date?->format('d.m.Y') ?: __('ui.pages.partner_organizations.show.values.signed_date_missing') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Agreement::STATUS_TRANSLATION_KEY.'.'.$agreement->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.partner_organizations.show.related.agreements_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['events'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.related.events_title') }}</h2>
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
                                    {{ $event->eventType?->display_name ?: __('ui.pages.partner_organizations.show.values.type_missing') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Event::FORMAT_TRANSLATION_KEY.'.'.$event->format) }}
                                </span>
                                <span>
                                    {{ $event->start_datetime?->format('d.m.Y H:i') ?: __('ui.pages.partner_organizations.show.values.start_datetime_missing') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Event::STATUS_TRANSLATION_KEY.'.'.$event->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.partner_organizations.show.related.events_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['visits'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.related.visits_title') }}</h2>
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
                                    {{ $visit->visitType?->display_name ?: __('ui.pages.partner_organizations.show.values.type_missing') }}
                                    {{ ' | ' }}
                                    {{ $visit->direction ? __(\App\Models\Visit::DIRECTION_TRANSLATION_KEY.'.'.$visit->direction) : __('ui.common.values.unknown') }}
                                </span>
                                <span>
                                    {{ $visit->start_date?->format('d.m.Y') ?: __('ui.pages.partner_organizations.show.values.start_date_missing') }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Visit::STATUS_TRANSLATION_KEY.'.'.$visit->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">{{ __('ui.pages.partner_organizations.show.related.visits_empty') }}</p>
                @endif
            </section>
        @endif

        @if ($relatedAccess['documents'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_organizations.show.eyebrows.related') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_organizations.show.related.documents_title') }}</h2>
                    </div>
                </div>

                @if ($documents->isNotEmpty())
                    <div class="stack-list">
                        @foreach ($documents as $document)
                            <article class="stack-list__item">
                                <strong>{{ $document->display_title }}</strong>
                                <span>
                                    {{ $document->documentType?->display_name ?: __('ui.pages.partner_organizations.show.values.type_missing') }}
                                    {{ ' | ' }}
                                    {{ strtoupper($document->file_ext ?: __('ui.pages.partner_organizations.show.values.file')) }}
                                    @if ($document->file_size_human)
                                        {{ ' | '.$document->file_size_human }}
                                    @endif
                                </span>
                                <span>
                                    {{ $document->uploader?->full_name ?: __('ui.pages.partner_organizations.show.values.uploader_missing') }}
                                    @if ($document->agreement)
                                        {{ ' | '.__('ui.pages.partner_organizations.show.values.related_agreement').': '.$document->agreement->display_title }}
                                    @endif
                                    @if ($document->visit)
                                        {{ ' | '.__('ui.pages.partner_organizations.show.values.related_visit').': '.$document->visit->display_title }}
                                    @endif
                                    @if ($document->event)
                                        {{ ' | '.__('ui.pages.partner_organizations.show.values.related_event').': '.$document->event->display_title }}
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
                    <p class="detail-empty">{{ __('ui.pages.partner_organizations.show.related.documents_empty') }}</p>
                @endif
            </section>
        @endif
    </div>
@endsection

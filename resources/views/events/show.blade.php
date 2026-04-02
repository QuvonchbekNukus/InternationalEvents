@extends('layouts.dashboard')

@section('title', $event->display_title)

@section('content')
    @php
        $statusClass = match ($event->status) {
            'hozirda' => 'is-active',
            'tugatilgan' => 'is-completed',
            'rejada' => 'is-planned',
            default => 'is-muted',
        };
        $canEdit = auth()->user()?->can('edit events')
            || (auth()->user()?->can('edit own events')
                && ((int) $event->responsible_user_id === (int) auth()->id()
                    || (int) $event->created_by === (int) auth()->id()));
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.events')]) }}</p>
                <h1 class="page-title">{{ $event->display_title }}</h1>
                <p class="page-subtitle">{{ __('ui.details.events.subtitle') }}</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('events.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>{{ __('ui.common.actions.back_to_list') }}</span>
                </a>

                @if ($canEdit)
                    <a class="btn btn--primary" href="{{ route('events.edit', $event) }}">
                        <i class="material-icons" aria-hidden="true">edit</i>
                        <span>{{ __('ui.common.actions.edit') }}</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="detail-grid">
            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.details.events.primary_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.events.primary_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.event_type') }}</span>
                        <strong>{{ $event->eventType?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.format') }}</span>
                        <strong>{{ $formats[$event->format] ?? $event->format }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.status') }}</span>
                        <span class="status-pill {{ $statusClass }}">{{ $statuses[$event->status] ?? $event->status }}</span>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.details.events.location_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.events.location_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.start_datetime') }}</span>
                        <strong>{{ $event->start_datetime?->format('d.m.Y H:i') ?: __('ui.common.values.not_entered') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.end_datetime') }}</span>
                        <strong>{{ $event->end_datetime?->format('d.m.Y H:i') ?: __('ui.common.values.not_entered') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.city') }}</span>
                        <strong>{{ $event->city ?: __('ui.common.values.not_entered') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.address') }}</span>
                        <strong>{{ $event->address ?: __('ui.common.values.not_entered') }}</strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.details.events.links_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.events.links_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.country') }}</span>
                        <strong>
                            @if ($event->country && auth()->user()?->can('view countries'))
                                <a class="row-title-link" href="{{ route('countries.show', $event->country) }}">{{ $event->country->display_name }}</a>
                            @else
                                {{ $event->country?->display_name ?: '-' }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.partner_organization') }}</span>
                        <strong>
                            @if ($event->partnerOrganization && auth()->user()?->can('view partner organizations'))
                                <a class="row-title-link" href="{{ route('partner-organizations.show', $event->partnerOrganization) }}">{{ $event->partnerOrganization->display_name }}</a>
                            @else
                                {{ $event->partnerOrganization?->display_name ?: __('ui.common.values.unassigned') }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.agreement') }}</span>
                        <strong>
                            @if ($event->agreement)
                                <a class="row-title-link" href="{{ route('agreements.show', $event->agreement) }}">{{ $event->agreement->display_title }}</a>
                            @else
                                {{ __('ui.common.values.unassigned') }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.responsible_user') }}</span>
                        <strong>{{ $event->responsibleUser?->full_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.responsible_department') }}</span>
                        <strong>{{ $event->responsibleDepartment?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.updater') }}</span>
                        <strong>{{ $event->updater?->full_name ?: __('ui.common.values.unknown') }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.description') }}</span>
                        <p class="detail-note">{{ $event->description ?: __('ui.common.values.no_description') }}</p>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.result') }}</span>
                        <p class="detail-note">
                            {{ $event->display_result_summary ?: __('ui.common.values.result_missing') }}
                        </p>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                @php
                    $documents = $event->documents->sortByDesc('created_at');
                    $imageDocuments = $documents->filter(fn ($document) => $document->is_image && $document->file_url);
                    $otherDocuments = $documents->reject(fn ($document) => $document->is_image && $document->file_url);
                @endphp

                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Biriktirilgan fayllar</p>
                        <h2 class="section-title">Tadbir hujjatlari</h2>
                    </div>
                </div>

                @if ($documents->isNotEmpty())
                    @if ($imageDocuments->isNotEmpty())
                        <div class="attachment-section">
                            <p class="attachment-section__label">Rasm previewlari</p>

                            <div class="document-gallery">
                                @foreach ($imageDocuments as $document)
                                    <article class="document-card">
                                        <a class="document-card__media" href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                            <img
                                                class="document-card__image"
                                                src="{{ $document->file_url }}"
                                                alt="{{ $document->display_title ?: $document->file_name }}"
                                                loading="lazy"
                                            >
                                        </a>

                                        <div class="document-card__body">
                                            <div class="document-card__meta">
                                                <strong>{{ $document->display_title }}</strong>
                                                <span>
                                                    {{ $document->file_name }}
                                                    {{ ' | ' }}
                                                    {{ strtoupper($document->file_ext ?: 'fayl') }}
                                                    @if ($document->file_size_human)
                                                        {{ ' | '.$document->file_size_human }}
                                                    @endif
                                                </span>
                                                <span>
                                                    {{ $document->uploader?->full_name ?: __('ui.common.values.unknown') }}
                                                    @if ($document->created_at)
                                                        {{ ' | '.$document->created_at->format('d.m.Y H:i') }}
                                                    @endif
                                                </span>
                                            </div>

                                            <div class="detail-actions-inline">
                                                <a class="action-pill" href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                                    <i class="material-icons" aria-hidden="true">open_in_new</i>
                                                    <span>Ochish</span>
                                                </a>
                                                <a class="action-pill" href="{{ $document->file_url }}" download="{{ $document->file_name }}">
                                                    <i class="material-icons" aria-hidden="true">file_download</i>
                                                    <span>Faylni olish</span>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($otherDocuments->isNotEmpty())
                        <div class="attachment-section">
                            <p class="attachment-section__label">Boshqa biriktirmalar</p>

                            <div class="stack-list">
                                @foreach ($otherDocuments as $document)
                                    <article class="stack-list__item">
                                        <strong>{{ $document->display_title }}</strong>
                                        <span>
                                            {{ $document->file_name }}
                                            {{ ' | ' }}
                                            {{ strtoupper($document->file_ext ?: 'fayl') }}
                                            @if ($document->file_size_human)
                                                {{ ' | '.$document->file_size_human }}
                                            @endif
                                        </span>
                                        <span>
                                            {{ $document->uploader?->full_name ?: __('ui.common.values.unknown') }}
                                            @if ($document->created_at)
                                                {{ ' | '.$document->created_at->format('d.m.Y H:i') }}
                                            @endif
                                        </span>
                                        @if ($document->file_url)
                                            <div class="detail-actions-inline">
                                                <a class="action-pill" href="{{ $document->file_url }}" target="_blank" rel="noopener">
                                                    <i class="material-icons" aria-hidden="true">open_in_new</i>
                                                    <span>Ochish</span>
                                                </a>
                                                <a class="action-pill" href="{{ $document->file_url }}" download="{{ $document->file_name }}">
                                                    <i class="material-icons" aria-hidden="true">file_download</i>
                                                    <span>Faylni olish</span>
                                                </a>
                                            </div>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <p class="detail-empty">Tadbir uchun hali fayl biriktirilmagan.</p>
                @endif
            </section>
        </div>
    </div>
@endsection

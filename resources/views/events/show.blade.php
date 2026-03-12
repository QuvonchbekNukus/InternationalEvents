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

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.control_due_date') }}</span>
                        <strong>{{ $event->control_due_date?->format('d.m.Y') ?: __('ui.common.values.not_entered') }}</strong>
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
                        <strong>{{ $event->country?->display_name ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.partner_organization') }}</span>
                        <strong>{{ $event->partnerOrganization?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.events.fields.agreement') }}</span>
                        <strong>{{ $event->agreement?->display_title ?: __('ui.common.values.unassigned') }}</strong>
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
        </div>
    </div>
@endsection

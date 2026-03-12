@extends('layouts.dashboard')

@section('title', $visit->display_title)

@section('content')
    @php
        $statusClass = match ($visit->status) {
            'ongoing' => 'is-active',
            'completed' => 'is-completed',
            'planned' => 'is-planned',
            default => 'is-muted',
        };
        $canEdit = auth()->user()?->can('edit visits')
            || (auth()->user()?->can('edit own visits')
                && ((int) $visit->responsible_user_id === (int) auth()->id()
                    || (int) $visit->created_by === (int) auth()->id()));
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.visits')]) }}</p>
                <h1 class="page-title">{{ $visit->display_title }}</h1>
                <p class="page-subtitle">{{ __('ui.details.visits.subtitle') }}</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('visits.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>{{ __('ui.common.actions.back_to_list') }}</span>
                </a>

                @if ($canEdit)
                    <a class="btn btn--primary" href="{{ route('visits.edit', $visit) }}">
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
                        <p class="eyebrow">{{ __('ui.details.visits.primary_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.visits.primary_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.visit_type') }}</span>
                        <strong>{{ $visit->visitType?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.direction') }}</span>
                        <strong>{{ $directions[$visit->direction] ?? __('ui.common.values.not_entered') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.status') }}</span>
                        <span class="status-pill {{ $statusClass }}">{{ $statuses[$visit->status] ?? $visit->status }}</span>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.duration') }}</span>
                        <strong>
                            {{ $visit->start_date?->format('d.m.Y') ?: '--' }}
                            {{ ' - ' }}
                            {{ $visit->end_date?->format('d.m.Y') ?: '--' }}
                        </strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.details.visits.location_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.visits.location_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.country') }}</span>
                        <strong>{{ $visit->country?->display_name ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.partner_organization') }}</span>
                        <strong>{{ $visit->partnerOrganization?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.city') }}</span>
                        <strong>{{ $visit->city ?: __('ui.common.values.not_entered') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.address') }}</span>
                        <strong>{{ $visit->address ?: __('ui.common.values.not_entered') }}</strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.details.visits.additional_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.visits.additional_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.responsible_user') }}</span>
                        <strong>{{ $visit->responsibleUser?->full_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.responsible_department') }}</span>
                        <strong>{{ $visit->responsibleDepartment?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.creator') }}</span>
                        <strong>{{ $visit->creator?->full_name ?: __('ui.common.values.unknown') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.updater') }}</span>
                        <strong>{{ $visit->updater?->full_name ?: __('ui.common.values.unknown') }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.purpose') }}</span>
                        <p class="detail-note">
                            {{ $visit->display_purpose ?: __('ui.common.values.purpose_missing') }}
                        </p>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.details.visits.fields.result') }}</span>
                        <p class="detail-note">
                            {{ $visit->display_result_summary ?: ($visit->description ?: __('ui.common.values.no_additional_info')) }}
                        </p>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

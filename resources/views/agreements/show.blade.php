@extends('layouts.dashboard')

@section('title', $agreement->display_title)

@section('content')
    @php
        $statusClass = match ($agreement->status) {
            'active' => 'is-active',
            'completed' => 'is-completed',
            'expired' => 'is-planned',
            default => 'is-muted',
        };
        $canEdit = auth()->user()?->can('edit agreements')
            || (auth()->user()?->can('edit own agreements')
                && ((int) $agreement->responsible_user_id === (int) auth()->id()
                    || (int) $agreement->created_by === (int) auth()->id()));
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreements')]) }}</p>
                <h1 class="page-title">{{ $agreement->display_title }}</h1>
                <p class="page-subtitle">{{ __('ui.details.agreements.subtitle') }}</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('agreements.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>{{ __('ui.common.actions.back_to_list') }}</span>
                </a>

                @if ($canEdit)
                    <a class="btn btn--primary" href="{{ route('agreements.edit', $agreement) }}">
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
                        <p class="eyebrow">{{ __('ui.details.agreements.primary_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.agreements.primary_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.number') }}</span>
                        <strong>{{ $agreement->agreement_number ?: __('ui.common.values.not_entered') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.status') }}</span>
                        <span class="status-pill {{ $statusClass }}">{{ $statuses[$agreement->status] ?? $agreement->status }}</span>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.signed_date') }}</span>
                        <strong>{{ $agreement->signed_date?->format('d.m.Y') ?: __('ui.common.values.not_entered') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.duration') }}</span>
                        <strong>
                            {{ $agreement->start_date?->format('d.m.Y') ?: '--' }}
                            {{ ' - ' }}
                            {{ $agreement->end_date?->format('d.m.Y') ?: '--' }}
                        </strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.details.agreements.links_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.agreements.links_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.country') }}</span>
                        <strong>{{ $agreement->country?->display_name ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.partner_organization') }}</span>
                        <strong>{{ $agreement->partnerOrganization?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.type') }}</span>
                        <strong>{{ $agreement->agreementType?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.direction') }}</span>
                        <strong>{{ $agreement->agreementDirection?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.responsible_user') }}</span>
                        <strong>{{ $agreement->responsibleUser?->full_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.responsible_department') }}</span>
                        <strong>{{ $agreement->responsibleDepartment?->display_name ?: __('ui.common.values.unassigned') }}</strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.details.agreements.additional_eyebrow') }}</p>
                        <h2 class="section-title">{{ __('ui.details.agreements.additional_title') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.short_titles') }}</span>
                        <strong>
                            {{ $agreement->display_short_title ?: __('ui.common.values.not_entered') }}
                        </strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.description') }}</span>
                        <p class="detail-note">{{ $agreement->description ?: __('ui.common.values.no_description') }}</p>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.creator') }}</span>
                        <strong>{{ $agreement->creator?->full_name ?: __('ui.common.values.unknown') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.details.agreements.fields.updater') }}</span>
                        <strong>{{ $agreement->updater?->full_name ?: __('ui.common.values.unknown') }}</strong>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

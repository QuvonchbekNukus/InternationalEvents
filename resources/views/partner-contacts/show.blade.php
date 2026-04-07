@extends('layouts.dashboard')

@section('title', $partnerContact->display_name)

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_contacts')]) }}</p>
                <h1 class="page-title">{{ $partnerContact->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_contacts.show.subtitle') }}</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('partner-contacts.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>{{ __('ui.common.actions.back_to_list') }}</span>
                </a>

                @can('edit partner contacts')
                    <a class="btn btn--primary" href="{{ route('partner-contacts.edit', $partnerContact) }}">
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
                        <p class="eyebrow">{{ __('ui.pages.partner_contacts.show.eyebrows.profile') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_contacts.show.sections.photo_profile') }}</h2>
                    </div>
                </div>

                <div class="detail-media">
                    @if ($partnerContact->photoDocument?->file_url)
                        <a href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">
                            <img
                                class="detail-media__thumb detail-media__thumb--large"
                                src="{{ $partnerContact->photoDocument->file_url }}"
                                alt="{{ $partnerContact->display_name }}"
                            >
                        </a>
                    @else
                        <span class="detail-media__thumb detail-media__thumb--large detail-media__thumb--placeholder">
                            <i class="material-icons" aria-hidden="true">person</i>
                        </span>
                    @endif

                    <div class="detail-media__body">
                        <strong>{{ $partnerContact->display_name }}</strong>
                        <span>{{ $partnerContact->display_position ?: __('ui.pages.partner_contacts.show.values.position_missing') }}</span>
                        <span>{{ $partnerContact->birthday?->format('d.m.Y') ?: __('ui.pages.partner_contacts.show.values.birthday_missing') }}</span>
                        <div class="detail-actions-inline">
                            <span class="status-pill {{ $partnerContact->is_primary ? 'is-active' : 'is-muted' }}">
                                {{ $partnerContact->is_primary ? __('ui.pages.partner_contacts.show.values.primary_contact') : __('ui.pages.partner_contacts.show.values.regular_contact') }}
                            </span>

                            @if ($partnerContact->photoDocument?->file_url)
                                <a class="action-pill" href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">
                                    <i class="material-icons" aria-hidden="true">image</i>
                                    <span>{{ __('ui.pages.partner_contacts.show.values.photo') }}</span>
                                </a>
                            @endif

                            @if ($partnerContact->cvDocument?->file_url)
                                <a class="action-pill" href="{{ $partnerContact->cvDocument->file_url }}" target="_blank" rel="noopener">
                                    <i class="material-icons" aria-hidden="true">description</i>
                                    <span>{{ __('ui.pages.partner_contacts.show.values.cv') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_contacts.show.eyebrows.connection') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_contacts.show.sections.contact_organization') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.organization') }}</span>
                        <strong>
                            @if ($partnerContact->partnerOrganization && auth()->user()?->can('view partner organizations'))
                                <a class="row-title-link" href="{{ route('partner-organizations.show', $partnerContact->partnerOrganization) }}">
                                    {{ $partnerContact->partnerOrganization->display_name }}
                                </a>
                            @else
                                {{ $partnerContact->partnerOrganization?->display_name ?: __('ui.pages.partner_contacts.show.values.organization_missing') }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.country') }}</span>
                        <strong>
                            @if ($partnerContact->partnerOrganization?->country && auth()->user()?->can('view countries'))
                                <a class="row-title-link" href="{{ route('countries.show', $partnerContact->partnerOrganization->country) }}">
                                    {{ $partnerContact->partnerOrganization->country->display_name }}
                                </a>
                            @else
                                {{ $partnerContact->partnerOrganization?->country?->display_name ?: __('ui.pages.partner_contacts.show.values.country_missing') }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.email') }}</span>
                        <strong>{{ $partnerContact->email ?: __('ui.pages.partner_contacts.show.values.email_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.phone') }}</span>
                        <strong>{{ $partnerContact->phone ?: __('ui.pages.partner_contacts.show.values.phone_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.organization_type') }}</span>
                        <strong>{{ $partnerContact->partnerOrganization?->organizationType?->display_name ?: __('ui.pages.partner_contacts.show.values.type_missing') }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.organization_website') }}</span>
                        <strong>
                            @if ($partnerContact->partnerOrganization?->website_url)
                                <a class="row-title-link" href="{{ $partnerContact->partnerOrganization->website_url }}" target="_blank" rel="noreferrer">
                                    {{ $partnerContact->partnerOrganization->website }}
                                </a>
                            @else
                                {{ __('ui.pages.partner_contacts.show.values.website_missing') }}
                            @endif
                        </strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_contacts.show.eyebrows.attachments') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_contacts.show.sections.photo_cv_details') }}</h2>
                    </div>
                </div>

                <div class="stack-list">
                    <article class="stack-list__item">
                        <strong>{{ $partnerContact->photoDocument?->display_title ?: __('ui.pages.partner_contacts.show.values.photo_missing') }}</strong>
                        <span>
                            @if ($partnerContact->photoDocument)
                                {{ $partnerContact->photoDocument->file_name }}
                                {{ ' | ' }}
                                {{ strtoupper($partnerContact->photoDocument->file_ext ?: __('ui.pages.partner_contacts.show.values.file')) }}
                                @if ($partnerContact->photoDocument->file_size_human)
                                    {{ ' | '.$partnerContact->photoDocument->file_size_human }}
                                @endif
                            @else
                                {{ __('ui.pages.partner_contacts.show.values.photo_document_missing') }}
                            @endif
                        </span>
                        @if ($partnerContact->photoDocument)
                            <div class="detail-actions-inline">
                                @if ($partnerContact->photoDocument->file_url)
                                    <a class="action-pill" href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">
                                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                                        <span>{{ __('ui.common.actions.open') }}</span>
                                    </a>
                                    <a class="action-pill" href="{{ $partnerContact->photoDocument->file_url }}" download="{{ $partnerContact->photoDocument->file_name }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>{{ __('ui.common.actions.download_file') }}</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </article>

                    <article class="stack-list__item">
                        <strong>{{ $partnerContact->cvDocument?->display_title ?: __('ui.pages.partner_contacts.show.values.cv_missing') }}</strong>
                        <span>
                            @if ($partnerContact->cvDocument)
                                {{ $partnerContact->cvDocument->file_name }}
                                {{ ' | ' }}
                                {{ strtoupper($partnerContact->cvDocument->file_ext ?: __('ui.pages.partner_contacts.show.values.file')) }}
                                @if ($partnerContact->cvDocument->file_size_human)
                                    {{ ' | '.$partnerContact->cvDocument->file_size_human }}
                                @endif
                            @else
                                {{ __('ui.pages.partner_contacts.show.values.cv_document_missing') }}
                            @endif
                        </span>
                        @if ($partnerContact->cvDocument)
                            <div class="detail-actions-inline">
                                @if ($partnerContact->cvDocument->file_url)
                                    <a class="action-pill" href="{{ $partnerContact->cvDocument->file_url }}" target="_blank" rel="noopener">
                                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                                        <span>{{ __('ui.common.actions.open') }}</span>
                                    </a>
                                    <a class="action-pill" href="{{ $partnerContact->cvDocument->file_url }}" download="{{ $partnerContact->cvDocument->file_name }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>{{ __('ui.common.actions.download_file') }}</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">{{ __('ui.pages.partner_contacts.show.eyebrows.additional') }}</p>
                        <h2 class="section-title">{{ __('ui.pages.partner_contacts.show.sections.language_description') }}</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.full_name_variants') }}</span>
                        <strong>{{ $partnerContact->full_name_ru ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.position_variants') }}</span>
                        <strong>{{ $partnerContact->position_ru ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">{{ __('ui.pages.partner_contacts.show.labels.description') }}</span>
                        <p class="detail-note">{{ $partnerContact->description ?: __('ui.common.values.no_additional_info') }}</p>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

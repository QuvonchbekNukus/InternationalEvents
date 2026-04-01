@extends('layouts.dashboard')

@section('title', $partnerContact->display_name)

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_contacts')]) }}</p>
                <h1 class="page-title">{{ $partnerContact->display_name }}</h1>
                <p class="page-subtitle">Hamkor kontaktning to'liq profili, tashkilot bog'lanishi va biriktirilgan foto hamda CV fayllari shu sahifada ko'rinadi.</p>
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
                        <p class="eyebrow">Profil</p>
                        <h2 class="section-title">Foto va asosiy ko'rinish</h2>
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
                        <span>{{ $partnerContact->display_position ?: "Lavozim ko'rsatilmagan" }}</span>
                        <span>{{ $partnerContact->birthday?->format('d.m.Y') ?: "Tug'ilgan sana kiritilmagan" }}</span>
                        <div class="detail-actions-inline">
                            <span class="status-pill {{ $partnerContact->is_primary ? 'is-active' : 'is-muted' }}">
                                {{ $partnerContact->is_primary ? 'Asosiy kontakt' : 'Oddiy kontakt' }}
                            </span>

                            @if ($partnerContact->photoDocument?->file_url)
                                <a class="action-pill" href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">
                                    <i class="material-icons" aria-hidden="true">image</i>
                                    <span>Foto</span>
                                </a>
                            @endif

                            @if ($partnerContact->cvDocument?->file_url)
                                <a class="action-pill" href="{{ $partnerContact->cvDocument->file_url }}" target="_blank" rel="noopener">
                                    <i class="material-icons" aria-hidden="true">description</i>
                                    <span>CV</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanish</p>
                        <h2 class="section-title">Aloqa va tashkilot</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Tashkilot</span>
                        <strong>
                            @if ($partnerContact->partnerOrganization && auth()->user()?->can('view partner organizations'))
                                <a class="row-title-link" href="{{ route('partner-organizations.show', $partnerContact->partnerOrganization) }}">
                                    {{ $partnerContact->partnerOrganization->display_name }}
                                </a>
                            @else
                                {{ $partnerContact->partnerOrganization?->display_name ?: "Tashkilot biriktirilmagan" }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Davlat</span>
                        <strong>
                            @if ($partnerContact->partnerOrganization?->country && auth()->user()?->can('view countries'))
                                <a class="row-title-link" href="{{ route('countries.show', $partnerContact->partnerOrganization->country) }}">
                                    {{ $partnerContact->partnerOrganization->country->display_name }}
                                </a>
                            @else
                                {{ $partnerContact->partnerOrganization?->country?->display_name ?: "Davlat ko'rsatilmagan" }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Email</span>
                        <strong>{{ $partnerContact->email ?: "Email kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Telefon</span>
                        <strong>{{ $partnerContact->phone ?: "Telefon kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Tashkilot turi</span>
                        <strong>{{ $partnerContact->partnerOrganization?->organizationType?->display_name ?: "Tur ko'rsatilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Tashkilot sayti</span>
                        <strong>
                            @if ($partnerContact->partnerOrganization?->website_url)
                                <a class="row-title-link" href="{{ $partnerContact->partnerOrganization->website_url }}" target="_blank" rel="noreferrer">
                                    {{ $partnerContact->partnerOrganization->website }}
                                </a>
                            @else
                                Sayt ko'rsatilmagan
                            @endif
                        </strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Biriktirilgan fayllar</p>
                        <h2 class="section-title">Foto va CV tafsilotlari</h2>
                    </div>
                </div>

                <div class="stack-list">
                    <article class="stack-list__item">
                        <strong>{{ $partnerContact->photoDocument?->display_title ?: "Foto biriktirilmagan" }}</strong>
                        <span>
                            @if ($partnerContact->photoDocument)
                                {{ $partnerContact->photoDocument->file_name }}
                                {{ ' | ' }}
                                {{ strtoupper($partnerContact->photoDocument->file_ext ?: 'fayl') }}
                                @if ($partnerContact->photoDocument->file_size_human)
                                    {{ ' | '.$partnerContact->photoDocument->file_size_human }}
                                @endif
                            @else
                                Kontakt uchun foto hujjati mavjud emas.
                            @endif
                        </span>
                        @if ($partnerContact->photoDocument)
                            <div class="detail-actions-inline">
                                @if ($partnerContact->photoDocument->file_url)
                                    <a class="action-pill" href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">
                                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                                        <span>Ochish</span>
                                    </a>
                                    <a class="action-pill" href="{{ $partnerContact->photoDocument->file_url }}" download="{{ $partnerContact->photoDocument->file_name }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>Faylni olish</span>
                                    </a>
                                @endif
                            </div>
                        @endif
                    </article>

                    <article class="stack-list__item">
                        <strong>{{ $partnerContact->cvDocument?->display_title ?: "CV biriktirilmagan" }}</strong>
                        <span>
                            @if ($partnerContact->cvDocument)
                                {{ $partnerContact->cvDocument->file_name }}
                                {{ ' | ' }}
                                {{ strtoupper($partnerContact->cvDocument->file_ext ?: 'fayl') }}
                                @if ($partnerContact->cvDocument->file_size_human)
                                    {{ ' | '.$partnerContact->cvDocument->file_size_human }}
                                @endif
                            @else
                                Kontakt uchun CV hujjati mavjud emas.
                            @endif
                        </span>
                        @if ($partnerContact->cvDocument)
                            <div class="detail-actions-inline">
                                @if ($partnerContact->cvDocument->file_url)
                                    <a class="action-pill" href="{{ $partnerContact->cvDocument->file_url }}" target="_blank" rel="noopener">
                                        <i class="material-icons" aria-hidden="true">open_in_new</i>
                                        <span>Ochish</span>
                                    </a>
                                    <a class="action-pill" href="{{ $partnerContact->cvDocument->file_url }}" download="{{ $partnerContact->cvDocument->file_name }}">
                                        <i class="material-icons" aria-hidden="true">file_download</i>
                                        <span>Faylni olish</span>
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
                        <p class="eyebrow">Qo'shimcha</p>
                        <h2 class="section-title">Til variantlari va izoh</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">F.I.Sh variantlari</span>
                        <strong>{{ $partnerContact->full_name_ru ?: '-' }} / {{ $partnerContact->full_name_cryl ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Lavozim variantlari</span>
                        <strong>{{ $partnerContact->position_ru ?: '-' }} / {{ $partnerContact->position_cryl ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Izoh</span>
                        <p class="detail-note">{{ $partnerContact->description ?: "Qo'shimcha izoh kiritilmagan." }}</p>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

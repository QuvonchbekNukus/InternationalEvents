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
                <p class="page-subtitle">Hamkor tashkilot bo'yicha asosiy rekvizitlar, kontaktlar va unga bog'langan jarayonlar shu sahifada ko'rsatiladi.</p>
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
                        <p class="eyebrow">Asosiy</p>
                        <h2 class="section-title">Tashkilot ma'lumotlari</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Davlat</span>
                        <strong>
                            @if ($partnerOrganization->country && auth()->user()?->can('view countries'))
                                <a class="row-title-link" href="{{ route('countries.show', $partnerOrganization->country) }}">
                                    {{ $partnerOrganization->country->display_name }}
                                </a>
                            @else
                                {{ $partnerOrganization->country?->display_name ?: "Davlat biriktirilmagan" }}
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Tashkilot turi</span>
                        <strong>{{ $partnerOrganization->organizationType?->display_name ?: "Tur ko'rsatilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Qisqa nom</span>
                        <strong>{{ $partnerOrganization->short_name ?: "Qisqa nom kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Info fayli</span>
                        <strong>{{ $partnerOrganization->organizationInfoDocument?->file_name ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Holat</span>
                        <span class="status-pill {{ $statusClass }}">
                            {{ $statuses[$partnerOrganization->status] ?? $partnerOrganization->status }}
                        </span>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Aloqa</p>
                        <h2 class="section-title">Joylashuv va izoh</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Shahar</span>
                        <strong>{{ $partnerOrganization->city ?: "Shahar ko'rsatilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Manzil</span>
                        <strong>{{ $partnerOrganization->address ?: "Manzil kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Veb-sayt</span>
                        <strong>
                            @if ($partnerOrganization->website_url)
                                <a class="row-title-link" href="{{ $partnerOrganization->website_url }}" target="_blank" rel="noreferrer">
                                    {{ $partnerOrganization->website }}
                                </a>
                            @else
                                Sayt ko'rsatilmagan
                            @endif
                        </strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Izoh</span>
                        <p class="detail-note">{{ $partnerOrganization->notes ?: "Qo'shimcha izoh kiritilmagan." }}</p>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Info fayli amallari</span>
                        @if ($partnerOrganization->organizationInfoDocument?->file_url)
                            <strong>{{ $partnerOrganization->organizationInfoDocument->display_title }}</strong>
                            <div class="detail-actions-inline">
                                <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" target="_blank" rel="noopener">
                                    <i class="material-icons" aria-hidden="true">open_in_new</i>
                                    <span>Ochish</span>
                                </a>
                                <a class="action-pill" href="{{ $partnerOrganization->organizationInfoDocument->file_url }}" download="{{ $partnerOrganization->organizationInfoDocument->file_name }}">
                                    <i class="material-icons" aria-hidden="true">file_download</i>
                                    <span>Faylni olish</span>
                                </a>
                            </div>
                        @else
                            <p class="detail-empty">Tashkilot uchun alohida info fayli biriktirilmagan.</p>
                        @endif
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Hamkorlik</p>
                        <h2 class="section-title">Hamkorlik tarixi</h2>
                    </div>
                </div>

                @if ($partnerOrganization->partnership_history)
                    <div class="detail-note detail-note--preserve-lines">{{ $partnerOrganization->partnership_history }}</div>
                @else
                    <p class="detail-empty">Hamkorlik tarixi hali kiritilmagan.</p>
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
                    <h2 class="stat-title">Kontaktlar</h2>
                    <p class="stat-description">Tashkilotga biriktirilgan kontaktlar soni.</p>
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
                    <p class="stat-description">Tashkilot bilan bog'langan kelishuvlar.</p>
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
                    <p class="stat-description">Tashkilot ishtirok etgan tadbirlar soni.</p>
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
                    <p class="stat-description">Tashkilot bilan bog'liq tashriflar.</p>
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
                    <p class="stat-description">Tashkilot bilan bog'langan fayllar va ilovalar.</p>
                </article>
            @endif
        </div>

        @if ($relatedAccess['partner_contacts'])
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Kontaktlar</h2>
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
                                    {{ $partnerContact->display_position ?: "Lavozim ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ $partnerContact->birthday?->format('d.m.Y') ?: "Tug'ilgan sana yo'q" }}
                                </span>
                                <span>
                                    {{ $partnerContact->email ?: "Email yo'q" }}
                                    {{ ' | ' }}
                                    {{ $partnerContact->phone ?: "Telefon yo'q" }}
                                </span>
                                <div class="detail-actions-inline">
                                    @if ($partnerContact->is_primary)
                                        <span class="status-pill is-active">Asosiy kontakt</span>
                                    @endif

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
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu tashkilotga hali kontakt biriktirilmagan.</p>
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
                                    {{ $agreement->agreementType?->display_name ?: "Tur ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ $agreement->agreementDirection?->display_name ?: "Yo'nalish ko'rsatilmagan" }}
                                </span>
                                <span>
                                    {{ $agreement->signed_date?->format('d.m.Y') ?: "Imzolangan sana yo'q" }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Agreement::STATUS_TRANSLATION_KEY.'.'.$agreement->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu tashkilot bo'yicha ko'rinadigan kelishuv topilmadi.</p>
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
                                    {{ $event->eventType?->display_name ?: "Tur ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Event::FORMAT_TRANSLATION_KEY.'.'.$event->format) }}
                                </span>
                                <span>
                                    {{ $event->start_datetime?->format('d.m.Y H:i') ?: "Boshlanish vaqti yo'q" }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Event::STATUS_TRANSLATION_KEY.'.'.$event->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu tashkilot bilan bog'langan tadbir topilmadi.</p>
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
                                    {{ $visit->visitType?->display_name ?: "Tur ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ $visit->direction ? __(\App\Models\Visit::DIRECTION_TRANSLATION_KEY.'.'.$visit->direction) : __('ui.common.values.unknown') }}
                                </span>
                                <span>
                                    {{ $visit->start_date?->format('d.m.Y') ?: "Boshlanish sanasi yo'q" }}
                                    {{ ' | ' }}
                                    {{ __(\App\Models\Visit::STATUS_TRANSLATION_KEY.'.'.$visit->status) }}
                                </span>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="detail-empty">Bu tashkilot bilan bog'langan tashrif topilmadi.</p>
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
                                    {{ $document->documentType?->display_name ?: "Tur ko'rsatilmagan" }}
                                    {{ ' | ' }}
                                    {{ strtoupper($document->file_ext ?: 'fayl') }}
                                    @if ($document->file_size_human)
                                        {{ ' | '.$document->file_size_human }}
                                    @endif
                                </span>
                                <span>
                                    {{ $document->uploader?->full_name ?: "Yuklovchi ko'rsatilmagan" }}
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
                    <p class="detail-empty">Bu tashkilot bilan bog'langan ko'rinadigan hujjat topilmadi.</p>
                @endif
            </section>
        @endif
    </div>
@endsection

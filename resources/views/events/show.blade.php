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
                <p class="eyebrow">CRUD / Events</p>
                <h1 class="page-title">{{ $event->display_title }}</h1>
                <p class="page-subtitle">Tadbirning vaqt, joy, natija va mas'ullar bo'yicha batafsil ko'rinishi.</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('events.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>Ro'yxatga qaytish</span>
                </a>

                @if ($canEdit)
                    <a class="btn btn--primary" href="{{ route('events.edit', $event) }}">
                        <i class="material-icons" aria-hidden="true">edit</i>
                        <span>Tahrirlash</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="detail-grid">
            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Asosiy ma'lumotlar</p>
                        <h2 class="section-title">Tadbir tafsilotlari</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Tadbir turi</span>
                        <strong>{{ $event->eventType?->name_uz ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Format</span>
                        <strong>{{ $formats[$event->format] ?? $event->format }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Holat</span>
                        <span class="status-pill {{ $statusClass }}">{{ $statuses[$event->status] ?? $event->status }}</span>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Nazorat sanasi</span>
                        <strong>{{ $event->control_due_date?->format('d.m.Y') ?: "Kiritilmagan" }}</strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Joy va vaqt</p>
                        <h2 class="section-title">Lokatsiya</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Boshlanish vaqti</span>
                        <strong>{{ $event->start_datetime?->format('d.m.Y H:i') ?: "Kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Tugash vaqti</span>
                        <strong>{{ $event->end_datetime?->format('d.m.Y H:i') ?: "Kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Shahar</span>
                        <strong>{{ $event->city ?: "Kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Manzil</span>
                        <strong>{{ $event->address ?: "Kiritilmagan" }}</strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Mas'ullar va natijalar</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Davlat</span>
                        <strong>{{ $event->country?->display_name ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Hamkor tashkilot</span>
                        <strong>{{ $event->partnerOrganization?->display_name ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Bog'liq kelishuv</span>
                        <strong>{{ $event->agreement?->short_title_uz ?: ($event->agreement?->title_uz ?: ($event->agreement?->title_ru ?: "Biriktirilmagan")) }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Javobgar foydalanuvchi</span>
                        <strong>{{ $event->responsibleUser?->full_name ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Javobgar bo'lim</span>
                        <strong>{{ $event->responsibleDepartment?->name_uz ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Oxirgi yangilagan</span>
                        <strong>{{ $event->updater?->full_name ?: "Noma'lum" }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Tavsif</span>
                        <p class="detail-note">{{ $event->description ?: "Tavsif kiritilmagan." }}</p>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Natija</span>
                        <p class="detail-note">
                            {{ $event->result_summary_uz ?: ($event->result_summary_ru ?: ($event->result_summary_cryl ?: "Natija kiritilmagan.")) }}
                        </p>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

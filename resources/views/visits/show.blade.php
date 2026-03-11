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
                <p class="eyebrow">CRUD / Visits</p>
                <h1 class="page-title">{{ $visit->display_title }}</h1>
                <p class="page-subtitle">Tashrifning maqsadi, muddati va biriktirilgan mas'ullar bo'yicha batafsil ko'rinish.</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('visits.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>Ro'yxatga qaytish</span>
                </a>

                @if ($canEdit)
                    <a class="btn btn--primary" href="{{ route('visits.edit', $visit) }}">
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
                        <h2 class="section-title">Tashrif tafsilotlari</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Tashrif turi</span>
                        <strong>{{ $visit->visitType?->name_uz ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Yo'nalish</span>
                        <strong>{{ $directions[$visit->direction] ?? "Kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Holat</span>
                        <span class="status-pill {{ $statusClass }}">{{ $statuses[$visit->status] ?? $visit->status }}</span>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Muddat</span>
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
                        <p class="eyebrow">Lokatsiya</p>
                        <h2 class="section-title">Davlat va manzil</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Davlat</span>
                        <strong>{{ $visit->country?->display_name ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Hamkor tashkilot</span>
                        <strong>{{ $visit->partnerOrganization?->display_name ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Shahar</span>
                        <strong>{{ $visit->city ?: "Kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Manzil</span>
                        <strong>{{ $visit->address ?: "Kiritilmagan" }}</strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Mas'ullar va mazmun</p>
                        <h2 class="section-title">Qo'shimcha ma'lumotlar</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Javobgar foydalanuvchi</span>
                        <strong>{{ $visit->responsibleUser?->full_name ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Javobgar bo'lim</span>
                        <strong>{{ $visit->responsibleDepartment?->name_uz ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Yaratgan foydalanuvchi</span>
                        <strong>{{ $visit->creator?->full_name ?: "Noma'lum" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Oxirgi yangilagan</span>
                        <strong>{{ $visit->updater?->full_name ?: "Noma'lum" }}</strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Maqsad</span>
                        <p class="detail-note">
                            {{ $visit->purpose_uz ?: ($visit->purpose_ru ?: ($visit->purpose_cryl ?: "Maqsad kiritilmagan.")) }}
                        </p>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Natija / izoh</span>
                        <p class="detail-note">
                            {{ $visit->result_summary_uz ?: ($visit->result_summary_ru ?: ($visit->result_summary_cryl ?: ($visit->description ?: "Qo'shimcha ma'lumot kiritilmagan."))) }}
                        </p>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

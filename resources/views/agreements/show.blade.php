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
                <p class="eyebrow">CRUD / Agreements</p>
                <h1 class="page-title">{{ $agreement->display_title }}</h1>
                <p class="page-subtitle">Kelishuv bo'yicha to'liq ma'lumot va unga biriktirilgan bog'lanishlar.</p>
            </div>

            <div class="form-actions">
                <a class="btn btn--ghost" href="{{ route('agreements.index') }}">
                    <i class="material-icons" aria-hidden="true">arrow_back</i>
                    <span>Ro'yxatga qaytish</span>
                </a>

                @if ($canEdit)
                    <a class="btn btn--primary" href="{{ route('agreements.edit', $agreement) }}">
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
                        <h2 class="section-title">Kelishuv tafsilotlari</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Kelishuv raqami</span>
                        <strong>{{ $agreement->agreement_number ?: "Kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Holat</span>
                        <span class="status-pill {{ $statusClass }}">{{ $statuses[$agreement->status] ?? $agreement->status }}</span>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Imzolangan sana</span>
                        <strong>{{ $agreement->signed_date?->format('d.m.Y') ?: "Kiritilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Amal qilish muddati</span>
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
                        <p class="eyebrow">Bog'lanishlar</p>
                        <h2 class="section-title">Mas'ullar va obyektlar</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item">
                        <span class="detail-list__label">Davlat</span>
                        <strong>{{ $agreement->country?->display_name ?: '-' }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Hamkor tashkilot</span>
                        <strong>{{ $agreement->partnerOrganization?->display_name ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Kelishuv turi</span>
                        <strong>{{ $agreement->agreementType?->name_uz ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Yo'nalish</span>
                        <strong>{{ $agreement->agreementDirection?->name_uz ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Javobgar foydalanuvchi</span>
                        <strong>{{ $agreement->responsibleUser?->full_name ?: "Biriktirilmagan" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Javobgar bo'lim</span>
                        <strong>{{ $agreement->responsibleDepartment?->name_uz ?: "Biriktirilmagan" }}</strong>
                    </article>
                </div>
            </section>

            <section class="content-card detail-card detail-card--full">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Qo'shimcha</p>
                        <h2 class="section-title">Izoh va audit</h2>
                    </div>
                </div>

                <div class="detail-list">
                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Qisqa nomlar</span>
                        <strong>
                            {{ $agreement->short_title_uz ?: ($agreement->short_title_ru ?: ($agreement->short_title_cryl ?: "Kiritilmagan")) }}
                        </strong>
                    </article>

                    <article class="detail-list__item detail-list__item--full">
                        <span class="detail-list__label">Tavsif</span>
                        <p class="detail-note">{{ $agreement->description ?: "Izoh kiritilmagan." }}</p>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Yaratgan foydalanuvchi</span>
                        <strong>{{ $agreement->creator?->full_name ?: "Noma'lum" }}</strong>
                    </article>

                    <article class="detail-list__item">
                        <span class="detail-list__label">Oxirgi yangilagan</span>
                        <strong>{{ $agreement->updater?->full_name ?: "Noma'lum" }}</strong>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

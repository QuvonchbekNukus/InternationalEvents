@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    @php
        $currentRole = auth()->user()?->getRoleNames()->first();
        $resourceCards = [
            [
                'permission' => 'view users',
                'title' => 'Foydalanuvchilar',
                'count' => \App\Models\User::count(),
                'description' => 'Tizimdagi xodimlar, lavozim va rollar holati.',
                'icon' => 'groups',
                'route' => route('users.index'),
                'action' => 'Boshqarish',
            ],
            [
                'permission' => 'view departments',
                'title' => "Bo'limlar",
                'count' => \App\Models\Department::count(),
                'description' => "Ichki bo'limlar va ularning biriktirilgan xodimlari.",
                'icon' => 'apartment',
                'route' => route('departments.index'),
                'action' => "Bo'limlarni ko'rish",
            ],
            [
                'permission' => 'view ranks',
                'title' => 'Unvonlar',
                'count' => \App\Models\Rank::count(),
                'description' => 'Harbiy unvonlar va foydalanuvchi birikmalari.',
                'icon' => 'military_tech',
                'route' => route('ranks.index'),
                'action' => "Unvonlarni ko'rish",
            ],
            [
                'permission' => 'view countries',
                'title' => 'Davlatlar',
                'count' => \App\Models\Country::count(),
                'description' => 'Hamkorlik qilinayotgan davlatlar va ularning holati.',
                'icon' => 'public',
                'route' => route('countries.index'),
                'action' => "Davlatlarni ko'rish",
            ],
            [
                'permission' => 'view organization types',
                'title' => 'Tashkilot turlari',
                'count' => \App\Models\OrganizationType::count(),
                'description' => "Hamkor subyektlar uchun tashkilot toifalari ro'yxati.",
                'icon' => 'domain',
                'route' => route('organization-types.index'),
                'action' => "Turlarni ko'rish",
            ],
        ];
    @endphp

    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">Bosh sahifa</p>
                <h1 class="page-title">Boshqaruv paneli</h1>
                <p class="page-subtitle">
                    Foydalanuvchilar, bo'limlar, unvonlar, davlatlar va tashkilot turlari modullari bitta kabinet ichida boshqariladi.
                </p>
            </div>

            <div class="context-chip">
                <i class="material-icons" aria-hidden="true">shield</i>
                <span>{{ $currentRole ? \Illuminate\Support\Str::headline(str_replace('-', ' ', $currentRole)) : 'Rol biriktirilmagan' }}</span>
            </div>
        </div>

        <div class="stats-grid">
            @foreach ($resourceCards as $card)
                @if (auth()->user()?->can($card['permission']))
                    <article class="stat-card">
                        <div class="stat-card__head">
                            <span class="stat-icon">
                                <i class="material-icons" aria-hidden="true">{{ $card['icon'] }}</i>
                            </span>
                            <a class="text-link" href="{{ $card['route'] }}">{{ $card['action'] }}</a>
                        </div>

                        <strong class="stat-value">{{ $card['count'] }}</strong>
                        <h2 class="stat-title">{{ $card['title'] }}</h2>
                        <p class="stat-description">{{ $card['description'] }}</p>
                    </article>
                @endif
            @endforeach
        </div>

        <div class="content-grid">
            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Permission tizimi</p>
                        <h2 class="section-title">Rollar va huquqlar</h2>
                    </div>
                </div>

                <div class="stack-list">
                    <article class="stack-list__item">
                        <strong>Super Admin</strong>
                        <span>Barcha CRUD amallari va foydalanuvchi o'chirish huquqiga ega.</span>
                    </article>

                    <article class="stack-list__item">
                        <strong>Admin</strong>
                        <span>Foydalanuvchi yaratish/tahrirlash, bo'lim, unvon, davlat va tashkilot turlarini to'liq boshqaradi.</span>
                    </article>

                    <article class="stack-list__item">
                        <strong>Operator</strong>
                        <span>Users, departments, ranks, countries va organization types jadvallarini faqat ko'rish huquqiga ega.</span>
                    </article>
                </div>
            </section>

            <section class="content-card">
                <div class="section-heading">
                    <div>
                        <p class="eyebrow">Tezkor eslatma</p>
                        <h2 class="section-title">Ish jarayoni</h2>
                    </div>
                </div>

                <div class="stack-list">
                    <article class="stack-list__item">
                        <strong>1. Ruxsatlar seed orqali yaratiladi</strong>
                        <span>`PermissionSeeder` va `RoleSeeder` mavjud rollarni permissionlar bilan sinxronlaydi.</span>
                    </article>

                    <article class="stack-list__item">
                        <strong>2. Menyular permission boyicha korinadi</strong>
                        <span>Sidebar ichidagi CRUD sahifalar faqat ruxsati bor foydalanuvchiga chiqadi.</span>
                    </article>

                    <article class="stack-list__item">
                        <strong>3. Har bir controller action himoyalangan</strong>
                        <span>`permission:*` middleware har bir resource uchun alohida qo'llanadi.</span>
                    </article>
                </div>
            </section>
        </div>
    </div>
@endsection

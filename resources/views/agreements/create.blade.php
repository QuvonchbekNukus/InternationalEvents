@extends('layouts.dashboard')

@section('title', 'Yangi kelishuv')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreements</p>
                <h1 class="page-title">Yangi kelishuv</h1>
                <p class="page-subtitle">Kelishuvning asosiy ma'lumotlari, bog'lanishlari va muddatlarini kiriting.</p>
            </div>
        </div>

        @include('agreements._form', [
            'action' => route('agreements.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

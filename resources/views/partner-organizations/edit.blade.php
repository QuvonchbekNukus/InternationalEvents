@extends('layouts.dashboard')

@section('title', 'Hamkor tashkilotni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Partner Organizations</p>
                <h1 class="page-title">{{ $partnerOrganization->display_name }}</h1>
                <p class="page-subtitle">Hamkor tashkilotning asosiy ma'lumotlarini yangilang.</p>
            </div>
        </div>

        @include('partner-organizations._form', [
            'action' => route('partner-organizations.update', $partnerOrganization),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Yangi hamkor tashkilot')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Partner Organizations</p>
                <h1 class="page-title">Yangi hamkor tashkilot</h1>
                <p class="page-subtitle">Davlat, turi va nomlarini kiritib yangi hamkor tashkilotni yarating.</p>
            </div>
        </div>

        @include('partner-organizations._form', [
            'action' => route('partner-organizations.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

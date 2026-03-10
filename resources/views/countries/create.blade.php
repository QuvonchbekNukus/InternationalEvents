@extends('layouts.dashboard')

@section('title', 'Yangi davlat')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Countries</p>
                <h1 class="page-title">Yangi davlat</h1>
                <p class="page-subtitle">Hamkor davlat uchun nom, ISO kodlari, mintaqa va xarita parametrlarini kiriting.</p>
            </div>
        </div>

        @include('countries._form', [
            'action' => route('countries.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

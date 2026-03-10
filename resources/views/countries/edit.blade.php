@extends('layouts.dashboard')

@section('title', 'Davlatni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Countries</p>
                <h1 class="page-title">{{ $country->display_name }}</h1>
                <p class="page-subtitle">Davlat nomlari, mintaqa, koordinata va fayl yo'llarini yangilang.</p>
            </div>
        </div>

        @include('countries._form', [
            'action' => route('countries.update', $country),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

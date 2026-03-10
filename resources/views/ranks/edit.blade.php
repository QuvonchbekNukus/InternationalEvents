@extends('layouts.dashboard')

@section('title', 'Unvonni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Ranks</p>
                <h1 class="page-title">{{ $rank->name_uz }}</h1>
                <p class="page-subtitle">Tanlangan unvonning nomlarini yangilang.</p>
            </div>
        </div>

        @include('ranks._form', [
            'action' => route('ranks.update', $rank),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

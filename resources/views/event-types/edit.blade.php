@extends('layouts.dashboard')

@section('title', 'Tadbir turini tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Event Types</p>
                <h1 class="page-title">{{ $eventType->name_uz }}</h1>
                <p class="page-subtitle">Tadbir turining nomlarini yangilang.</p>
            </div>
        </div>

        @include('event-types._form', [
            'action' => route('event-types.update', $eventType),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

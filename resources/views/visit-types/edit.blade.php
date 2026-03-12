@extends('layouts.dashboard')

@section('title', 'Tashrif turini tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.visit_types')]) }}</p>
                <h1 class="page-title">{{ $visitType->display_name }}</h1>
                <p class="page-subtitle">Tashrif turining nomlarini yangilang.</p>
            </div>
        </div>

        @include('visit-types._form', [
            'action' => route('visit-types.update', $visitType),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

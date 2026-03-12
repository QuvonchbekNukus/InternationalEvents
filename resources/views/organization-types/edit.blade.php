@extends('layouts.dashboard')

@section('title', 'Tashkilot turini tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.organization_types')]) }}</p>
                <h1 class="page-title">{{ $organizationType->display_name }}</h1>
                <p class="page-subtitle">Tashkilot turining nomlarini yangilang.</p>
            </div>
        </div>

        @include('organization-types._form', [
            'action' => route('organization-types.update', $organizationType),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

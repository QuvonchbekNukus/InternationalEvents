@extends('layouts.dashboard')

@section('title', 'Yangi unvon')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.ranks')]) }}</p>
                <h1 class="page-title">Yangi unvon</h1>
                <p class="page-subtitle">Foydalanuvchilar uchun yangi harbiy unvon yarating.</p>
            </div>
        </div>

        @include('ranks._form', [
            'action' => route('ranks.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

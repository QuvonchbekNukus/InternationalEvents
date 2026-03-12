@extends('layouts.dashboard')

@section('title', 'Kelishuv turini tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreement_types')]) }}</p>
                <h1 class="page-title">{{ $agreementType->display_name }}</h1>
                <p class="page-subtitle">Kelishuv turining nomlarini yangilang.</p>
            </div>
        </div>

        @include('agreement-types._form', [
            'action' => route('agreement-types.update', $agreementType),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

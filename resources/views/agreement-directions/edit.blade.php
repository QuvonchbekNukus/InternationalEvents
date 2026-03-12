@extends('layouts.dashboard')

@section('title', "Kelishuv yo'nalishini tahrirlash")

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreement_directions')]) }}</p>
                <h1 class="page-title">{{ $agreementDirection->display_name }}</h1>
                <p class="page-subtitle">Kelishuv yo'nalishining nomlarini yangilang.</p>
            </div>
        </div>

        @include('agreement-directions._form', [
            'action' => route('agreement-directions.update', $agreementDirection),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

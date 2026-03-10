@extends('layouts.dashboard')

@section('title', 'Kelishuv turini tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreement Types</p>
                <h1 class="page-title">{{ $agreementType->name_uz }}</h1>
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

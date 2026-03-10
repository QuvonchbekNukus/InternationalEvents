@extends('layouts.dashboard')

@section('title', 'Yangi kelishuv turi')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreement Types</p>
                <h1 class="page-title">Yangi kelishuv turi</h1>
                <p class="page-subtitle">Kelishuv turining uch tildagi nomlarini kiriting.</p>
            </div>
        </div>

        @include('agreement-types._form', [
            'action' => route('agreement-types.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Yangi tashrif turi')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Visit Types</p>
                <h1 class="page-title">Yangi tashrif turi</h1>
                <p class="page-subtitle">Tashrif turining uch tildagi nomlarini kiriting.</p>
            </div>
        </div>

        @include('visit-types._form', [
            'action' => route('visit-types.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

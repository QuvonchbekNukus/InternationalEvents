@extends('layouts.dashboard')

@section('title', 'Yangi tashkilot turi')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Organization Types</p>
                <h1 class="page-title">Yangi tashkilot turi</h1>
                <p class="page-subtitle">Tashkilot turining uch tildagi nomlarini kiriting.</p>
            </div>
        </div>

        @include('organization-types._form', [
            'action' => route('organization-types.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

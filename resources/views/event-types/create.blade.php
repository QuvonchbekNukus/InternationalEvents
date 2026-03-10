@extends('layouts.dashboard')

@section('title', 'Yangi tadbir turi')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Event Types</p>
                <h1 class="page-title">Yangi tadbir turi</h1>
                <p class="page-subtitle">Tadbir turining uch tildagi nomlarini kiriting.</p>
            </div>
        </div>

        @include('event-types._form', [
            'action' => route('event-types.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

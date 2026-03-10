@extends('layouts.dashboard')

@section('title', 'Yangi hujjat turi')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Document Types</p>
                <h1 class="page-title">Yangi hujjat turi</h1>
                <p class="page-subtitle">Hujjat turining uch tildagi nomlarini kiriting.</p>
            </div>
        </div>

        @include('document-types._form', [
            'action' => route('document-types.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

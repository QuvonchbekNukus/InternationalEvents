@extends('layouts.dashboard')

@section('title', 'Hujjat turini tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Document Types</p>
                <h1 class="page-title">{{ $documentType->name_uz }}</h1>
                <p class="page-subtitle">Hujjat turining nomlarini yangilang.</p>
            </div>
        </div>

        @include('document-types._form', [
            'action' => route('document-types.update', $documentType),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

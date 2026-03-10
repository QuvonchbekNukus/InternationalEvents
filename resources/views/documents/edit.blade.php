@extends('layouts.dashboard')

@section('title', 'Hujjatni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Documents</p>
                <h1 class="page-title">{{ $document->display_title }}</h1>
                <p class="page-subtitle">Hujjat ma'lumotlarini yangilang yoki yangi fayl yuklang.</p>
            </div>
        </div>

        @include('documents._form', [
            'action' => route('documents.update', $document),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

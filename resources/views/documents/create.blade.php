@extends('layouts.dashboard')

@section('title', 'Yangi hujjat')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.documents')]) }}</p>
                <h1 class="page-title">Yangi hujjat</h1>
                <p class="page-subtitle">Hujjat ma'lumotlarini kiriting va faylni yuklang.</p>
            </div>
        </div>

        @include('documents._form', [
            'action' => route('documents.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Kelishuvni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreements</p>
                <h1 class="page-title">{{ $agreement->display_title }}</h1>
                <p class="page-subtitle">Kelishuvga tegishli ma'lumotlarni yangilang.</p>
            </div>
        </div>

        @include('agreements._form', [
            'action' => route('agreements.update', $agreement),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Tashrifni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Visits</p>
                <h1 class="page-title">{{ $visit->display_title }}</h1>
                <p class="page-subtitle">Tashrifga oid ma'lumotlarni yangilang.</p>
            </div>
        </div>

        @include('visits._form', [
            'action' => route('visits.update', $visit),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

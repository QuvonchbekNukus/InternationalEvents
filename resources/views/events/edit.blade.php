@extends('layouts.dashboard')

@section('title', 'Tadbirni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Events</p>
                <h1 class="page-title">{{ $event->display_title }}</h1>
                <p class="page-subtitle">Tadbirga oid ma'lumotlarni yangilang.</p>
            </div>
        </div>

        @include('events._form', [
            'action' => route('events.update', $event),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

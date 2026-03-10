@extends('layouts.dashboard')

@section('title', 'Yangi tadbir')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Events</p>
                <h1 class="page-title">Yangi tadbir</h1>
                <p class="page-subtitle">Tadbir bo'yicha asosiy ma'lumotlar, vaqt va natijalarni kiriting.</p>
            </div>
        </div>

        @include('events._form', [
            'action' => route('events.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

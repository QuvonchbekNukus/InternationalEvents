@extends('layouts.dashboard')

@section('title', 'Yangi tashrif')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Visits</p>
                <h1 class="page-title">Yangi tashrif</h1>
                <p class="page-subtitle">Tashrif bo'yicha asosiy ma'lumotlar, maqsad va natijalarni kiriting.</p>
            </div>
        </div>

        @include('visits._form', [
            'action' => route('visits.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

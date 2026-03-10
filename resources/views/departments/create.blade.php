@extends('layouts.dashboard')

@section('title', "Yangi bo'lim")

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Departments</p>
                <h1 class="page-title">Yangi bo'lim</h1>
                <p class="page-subtitle">Yangi tarkibiy bo'limni yaratib, qisqa kodi va tavsifini kiriting.</p>
            </div>
        </div>

        @include('departments._form', [
            'action' => route('departments.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

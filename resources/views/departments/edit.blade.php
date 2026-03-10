@extends('layouts.dashboard')

@section('title', "Bo'limni tahrirlash")

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Departments</p>
                <h1 class="page-title">{{ $department->name_uz }}</h1>
                <p class="page-subtitle">Bo'limning nomlari, kodi va tavsifini yangilang.</p>
            </div>
        </div>

        @include('departments._form', [
            'action' => route('departments.update', $department),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', 'Yangi foydalanuvchi')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Users</p>
                <h1 class="page-title">Yangi foydalanuvchi</h1>
                <p class="page-subtitle">Telefon raqami, rol, bo'lim va unvonni ko'rsatib yangi foydalanuvchi yarating.</p>
            </div>
        </div>

        @include('users._form', [
            'action' => route('users.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

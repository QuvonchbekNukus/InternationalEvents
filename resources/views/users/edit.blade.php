@extends('layouts.dashboard')

@section('title', 'Foydalanuvchini tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.users')]) }}</p>
                <h1 class="page-title">{{ $user->full_name }}</h1>
                <p class="page-subtitle">Foydalanuvchi ma'lumotlari, rol va statusni shu yerda yangilang.</p>
            </div>
        </div>

        @include('users._form', [
            'action' => route('users.update', $user),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

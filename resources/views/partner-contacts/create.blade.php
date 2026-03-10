@extends('layouts.dashboard')

@section('title', 'Yangi hamkor kontakt')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Partner Contacts</p>
                <h1 class="page-title">Yangi hamkor kontakt</h1>
                <p class="page-subtitle">Hamkor tashkilot uchun asosiy yoki oddiy kontakt ma'lumotlarini kiriting.</p>
            </div>
        </div>

        @include('partner-contacts._form', [
            'action' => route('partner-contacts.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

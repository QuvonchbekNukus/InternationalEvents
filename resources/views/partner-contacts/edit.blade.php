@extends('layouts.dashboard')

@section('title', 'Hamkor kontaktni tahrirlash')

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Partner Contacts</p>
                <h1 class="page-title">{{ $partnerContact->display_name }}</h1>
                <p class="page-subtitle">Hamkor kontaktning F.I.Sh, aloqa va lavozim ma'lumotlarini yangilang.</p>
            </div>
        </div>

        @include('partner-contacts._form', [
            'action' => route('partner-contacts.update', $partnerContact),
            'method' => 'PUT',
            'submitLabel' => 'Yangilash',
        ])
    </div>
@endsection

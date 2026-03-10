@extends('layouts.dashboard')

@section('title', "Yangi kelishuv yo'nalishi")

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">CRUD / Agreement Directions</p>
                <h1 class="page-title">Yangi kelishuv yo'nalishi</h1>
                <p class="page-subtitle">Hamkorlik yo'nalishining uch tildagi nomlarini kiriting.</p>
            </div>
        </div>

        @include('agreement-directions._form', [
            'action' => route('agreement-directions.store'),
            'method' => 'POST',
            'submitLabel' => 'Saqlash',
        ])
    </div>
@endsection

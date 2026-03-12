@extends('layouts.dashboard')

@section('title', __('ui.pages.agreements.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreements')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.agreements.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreements.create.subtitle') }}</p>
            </div>
        </div>

        @include('agreements._form', [
            'action' => route('agreements.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

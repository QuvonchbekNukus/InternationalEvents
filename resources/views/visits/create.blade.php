@extends('layouts.dashboard')

@section('title', __('ui.pages.visits.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.visits')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.visits.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.visits.create.subtitle') }}</p>
            </div>
        </div>

        @include('visits._form', [
            'action' => route('visits.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

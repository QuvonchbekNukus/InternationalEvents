@extends('layouts.dashboard')

@section('title', __('ui.pages.events.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.events')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.events.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.events.create.subtitle') }}</p>
            </div>
        </div>

        @include('events._form', [
            'action' => route('events.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

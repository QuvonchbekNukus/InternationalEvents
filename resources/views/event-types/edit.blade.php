@extends('layouts.dashboard')

@section('title', __('ui.pages.event_types.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.event_types')]) }}</p>
                <h1 class="page-title">{{ $eventType->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.event_types.edit.subtitle') }}</p>
            </div>
        </div>

        @include('event-types._form', [
            'action' => route('event-types.update', $eventType),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', __('ui.pages.events.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.events')]) }}</p>
                <h1 class="page-title">{{ $event->display_title }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.events.edit.subtitle') }}</p>
            </div>
        </div>

        @include('events._form', [
            'action' => route('events.update', $event),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

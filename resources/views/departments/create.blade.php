@extends('layouts.dashboard')

@section('title', __('ui.pages.departments.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.departments')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.departments.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.departments.create.subtitle') }}</p>
            </div>
        </div>

        @include('departments._form', [
            'action' => route('departments.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

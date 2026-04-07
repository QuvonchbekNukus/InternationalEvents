@extends('layouts.dashboard')

@section('title', __('ui.pages.departments.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.departments')]) }}</p>
                <h1 class="page-title">{{ $department->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.departments.edit.subtitle') }}</p>
            </div>
        </div>

        @include('departments._form', [
            'action' => route('departments.update', $department),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

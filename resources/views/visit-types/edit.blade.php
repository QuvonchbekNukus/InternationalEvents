@extends('layouts.dashboard')

@section('title', __('ui.pages.visit_types.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.visit_types')]) }}</p>
                <h1 class="page-title">{{ $visitType->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.visit_types.edit.subtitle') }}</p>
            </div>
        </div>

        @include('visit-types._form', [
            'action' => route('visit-types.update', $visitType),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

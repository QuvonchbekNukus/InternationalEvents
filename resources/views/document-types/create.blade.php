@extends('layouts.dashboard')

@section('title', __('ui.pages.document_types.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.document_types')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.document_types.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.document_types.create.subtitle') }}</p>
            </div>
        </div>

        @include('document-types._form', [
            'action' => route('document-types.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

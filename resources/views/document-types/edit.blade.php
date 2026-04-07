@extends('layouts.dashboard')

@section('title', __('ui.pages.document_types.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.document_types')]) }}</p>
                <h1 class="page-title">{{ $documentType->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.document_types.edit.subtitle') }}</p>
            </div>
        </div>

        @include('document-types._form', [
            'action' => route('document-types.update', $documentType),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

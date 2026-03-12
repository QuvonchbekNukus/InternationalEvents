@extends('layouts.dashboard')

@section('title', __('ui.pages.agreements.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreements')]) }}</p>
                <h1 class="page-title">{{ $agreement->display_title }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreements.edit.subtitle') }}</p>
            </div>
        </div>

        @include('agreements._form', [
            'action' => route('agreements.update', $agreement),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

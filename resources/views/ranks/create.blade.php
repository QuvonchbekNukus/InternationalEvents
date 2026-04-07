@extends('layouts.dashboard')

@section('title', __('ui.pages.ranks.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.ranks')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.ranks.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.ranks.create.subtitle') }}</p>
            </div>
        </div>

        @include('ranks._form', [
            'action' => route('ranks.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

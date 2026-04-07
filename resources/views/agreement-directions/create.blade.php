@extends('layouts.dashboard')

@section('title', __('ui.pages.agreement_directions.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreement_directions')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.agreement_directions.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreement_directions.create.subtitle') }}</p>
            </div>
        </div>

        @include('agreement-directions._form', [
            'action' => route('agreement-directions.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

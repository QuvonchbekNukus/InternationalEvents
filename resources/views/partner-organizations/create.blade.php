@extends('layouts.dashboard')

@section('title', __('ui.pages.partner_organizations.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_organizations')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.partner_organizations.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_organizations.create.subtitle') }}</p>
            </div>
        </div>

        @include('partner-organizations._form', [
            'action' => route('partner-organizations.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', __('ui.pages.partner_organizations.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_organizations')]) }}</p>
                <h1 class="page-title">{{ $partnerOrganization->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_organizations.edit.subtitle') }}</p>
            </div>
        </div>

        @include('partner-organizations._form', [
            'action' => route('partner-organizations.update', $partnerOrganization),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

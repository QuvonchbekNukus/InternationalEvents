@extends('layouts.dashboard')

@section('title', __('ui.pages.partner_contacts.create.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_contacts')]) }}</p>
                <h1 class="page-title">{{ __('ui.pages.partner_contacts.create.title') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_contacts.create.subtitle') }}</p>
            </div>
        </div>

        @include('partner-contacts._form', [
            'action' => route('partner-contacts.store'),
            'method' => 'POST',
            'submitLabel' => __('ui.common.actions.save'),
        ])
    </div>
@endsection

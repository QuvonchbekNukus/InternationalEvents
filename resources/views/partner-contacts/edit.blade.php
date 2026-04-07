@extends('layouts.dashboard')

@section('title', __('ui.pages.partner_contacts.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_contacts')]) }}</p>
                <h1 class="page-title">{{ $partnerContact->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_contacts.edit.subtitle') }}</p>
            </div>
        </div>

        @include('partner-contacts._form', [
            'action' => route('partner-contacts.update', $partnerContact),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', __('ui.pages.agreement_types.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreement_types')]) }}</p>
                <h1 class="page-title">{{ $agreementType->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreement_types.edit.subtitle') }}</p>
            </div>
        </div>

        @include('agreement-types._form', [
            'action' => route('agreement-types.update', $agreementType),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

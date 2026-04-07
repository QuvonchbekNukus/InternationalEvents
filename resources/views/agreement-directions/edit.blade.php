@extends('layouts.dashboard')

@section('title', __('ui.pages.agreement_directions.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreement_directions')]) }}</p>
                <h1 class="page-title">{{ $agreementDirection->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreement_directions.edit.subtitle') }}</p>
            </div>
        </div>

        @include('agreement-directions._form', [
            'action' => route('agreement-directions.update', $agreementDirection),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

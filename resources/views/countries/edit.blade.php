@extends('layouts.dashboard')

@section('title', __('ui.pages.countries.edit.title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.countries')]) }}</p>
                <h1 class="page-title">{{ $country->display_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.countries.edit.subtitle') }}</p>
            </div>
        </div>

        @include('countries._form', [
            'action' => route('countries.update', $country),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

@extends('layouts.dashboard')

@section('title', __('ui.pages.users.edit_title'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.users')]) }}</p>
                <h1 class="page-title">{{ $user->full_name }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.users.edit.subtitle') }}</p>
            </div>
        </div>

        @include('users._form', [
            'action' => route('users.update', $user),
            'method' => 'PUT',
            'submitLabel' => __('ui.common.actions.update'),
        ])
    </div>
@endsection

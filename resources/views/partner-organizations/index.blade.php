@extends('layouts.dashboard')

@section('title', __('ui.sidebar.partner_organizations'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_organizations')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.partner_organizations') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_organizations.index.subtitle') }}</p>
            </div>

            @can('create partner organizations')
                <a class="btn btn--primary" href="{{ route('partner-organizations.create') }}">
                    <i class="material-icons" aria-hidden="true">business</i>
                    <span>{{ __('ui.pages.partner_organizations.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('partner-organizations.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.partner_organizations.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.partner_organizations.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="{{ __('ui.pages.partner_organizations.index.country_filter') }}">
                <option value="">{{ __('ui.pages.partner_organizations.index.all_countries') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="organization_type_id" aria-label="{{ __('ui.pages.partner_organizations.index.type_filter') }}">
                <option value="">{{ __('ui.pages.partner_organizations.index.all_types') }}</option>
                @foreach ($organizationTypes as $organizationType)
                    <option value="{{ $organizationType->id }}" @selected((string) $filters['organization_type_id'] === (string) $organizationType->id)>{{ $organizationType->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="{{ __('ui.pages.partner_organizations.index.status_filter') }}">
                <option value="">{{ __('ui.pages.partner_organizations.index.all_statuses') }}</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('partner-organizations.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($partnerOrganizations->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.partner_organizations.index.headers.organization') }}</th>
                            <th>{{ __('ui.pages.partner_organizations.index.headers.country') }}</th>
                            <th>{{ __('ui.pages.partner_organizations.index.headers.type') }}</th>
                            <th>{{ __('ui.pages.partner_organizations.index.headers.address') }}</th>
                            <th>{{ __('ui.pages.partner_organizations.index.headers.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partnerOrganizations as $partnerOrganization)
                            @php
                                $statusClass = match ($partnerOrganization->status) {
                                    'rejada' => 'is-planned',
                                    'tugallangan' => 'is-completed',
                                    default => 'is-active',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">
                                        <a class="row-title-link" href="{{ route('partner-organizations.show', $partnerOrganization) }}">{{ $partnerOrganization->display_name }}</a>
                                    </span>
                                    <span class="row-subtitle">
                                        {{ $partnerOrganization->short_name ?: __('ui.pages.partner_organizations.index.values.short_name_missing') }}
                                        @if ($partnerOrganization->website)
                                            {{ ' - '.$partnerOrganization->website }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerOrganization->country?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $partnerOrganization->country?->iso2 ?: __('ui.pages.partner_organizations.index.values.iso_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerOrganization->organizationType?->display_name ?: __('ui.pages.partner_organizations.index.values.type_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerOrganization->city ?: __('ui.pages.partner_organizations.index.values.city_missing') }}</span>
                                    <span class="row-subtitle">{{ $partnerOrganization->address ?: __('ui.pages.partner_organizations.index.values.address_missing') }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$partnerOrganization->status] ?? $partnerOrganization->status }}
                                    </span>
                                    @if ($partnerOrganization->notes)
                                        <span class="row-subtitle">{{ $partnerOrganization->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit partner organizations')
                                            <a class="action-pill" href="{{ route('partner-organizations.edit', $partnerOrganization) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete partner organizations')
                                            <form
                                                method="POST"
                                                action="{{ route('partner-organizations.destroy', $partnerOrganization) }}"
                                                data-confirm-message="{{ __('ui.pages.partner_organizations.index.confirm_delete') }}"
                                                onsubmit="return confirm(this.dataset.confirmMessage);"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button class="action-pill action-pill--danger" type="submit">
                                                    <i class="material-icons" aria-hidden="true">delete</i>
                                                    <span>{{ __('ui.common.actions.delete') }}</span>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="table-empty">
                    {{ __('ui.pages.partner_organizations.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$partnerOrganizations" />
        </div>
    </div>
@endsection

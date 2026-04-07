@extends('layouts.dashboard')

@section('title', __('ui.sidebar.partner_contacts'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.partner_contacts')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.partner_contacts') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.partner_contacts.index.subtitle') }}</p>
            </div>

            @can('create partner contacts')
                <a class="btn btn--primary" href="{{ route('partner-contacts.create') }}">
                    <i class="material-icons" aria-hidden="true">perm_contact_calendar</i>
                    <span>{{ __('ui.pages.partner_contacts.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('partner-contacts.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.partner_contacts.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.partner_contacts.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="partner_organization_id" aria-label="{{ __('ui.pages.partner_contacts.index.organization_filter') }}">
                <option value="">{{ __('ui.pages.partner_contacts.index.all_organizations') }}</option>
                @foreach ($partnerOrganizations as $partnerOrganization)
                    <option value="{{ $partnerOrganization->id }}" @selected((string) $filters['partner_organization_id'] === (string) $partnerOrganization->id)>
                        {{ $partnerOrganization->display_name }}
                    </option>
                @endforeach
            </select>

            <select class="toolbar-select" name="primary" aria-label="{{ __('ui.pages.partner_contacts.index.primary_filter') }}">
                <option value="">{{ __('ui.pages.partner_contacts.index.all_contacts') }}</option>
                <option value="1" @selected($filters['primary'] === '1')>{{ __('ui.pages.partner_contacts.index.primary_only') }}</option>
                <option value="0" @selected($filters['primary'] === '0')>{{ __('ui.pages.partner_contacts.index.regular_only') }}</option>
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('partner-contacts.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($partnerContacts->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.partner_contacts.index.headers.contact') }}</th>
                            <th>{{ __('ui.pages.partner_contacts.index.headers.organization') }}</th>
                            <th>{{ __('ui.pages.partner_contacts.index.headers.birthday') }}</th>
                            <th>{{ __('ui.pages.partner_contacts.index.headers.position') }}</th>
                            <th>{{ __('ui.pages.partner_contacts.index.headers.contact_info') }}</th>
                            <th>{{ __('ui.pages.partner_contacts.index.headers.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partnerContacts as $partnerContact)
                            <tr>
                                <td>
                                    <span class="row-title">
                                        <a class="row-title-link" href="{{ route('partner-contacts.show', $partnerContact) }}">{{ $partnerContact->display_name }}</a>
                                    </span>
                                    <span class="row-subtitle">{{ $partnerContact->full_name_ru }}</span>
                                    @if ($partnerContact->photoDocument || $partnerContact->cvDocument)
                                        <span class="row-subtitle">
                                            @if ($partnerContact->photoDocument?->file_url)
                                                <a href="{{ $partnerContact->photoDocument->file_url }}" target="_blank" rel="noopener">{{ __('ui.pages.partner_contacts.index.values.photo') }}</a>
                                            @endif
                                            @if ($partnerContact->photoDocument?->file_url && $partnerContact->cvDocument?->file_url)
                                                /
                                            @endif
                                            @if ($partnerContact->cvDocument?->file_url)
                                                <a href="{{ $partnerContact->cvDocument->file_url }}" target="_blank" rel="noopener">{{ __('ui.pages.partner_contacts.index.values.cv') }}</a>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->partnerOrganization?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">
                                        {{ $partnerContact->partnerOrganization?->country?->display_name ?: __('ui.pages.partner_contacts.index.values.country_missing') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->birthday?->format('d.m.Y') ?: __('ui.common.values.not_entered') }}</span>
                                    @if ($partnerContact->birthday)
                                        <span class="row-subtitle">{{ __('ui.pages.partner_contacts.index.values.age', ['count' => $partnerContact->birthday->age]) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->display_position ?: __('ui.pages.partner_contacts.index.values.position_missing') }}</span>
                                    @if ($partnerContact->position_ru)
                                        <span class="row-subtitle">{{ $partnerContact->position_ru }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $partnerContact->email ?: __('ui.pages.partner_contacts.index.values.email_missing') }}</span>
                                    <span class="row-subtitle">{{ $partnerContact->phone ?: __('ui.pages.partner_contacts.index.values.phone_missing') }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $partnerContact->is_primary ? 'is-active' : 'is-muted' }}">
                                        {{ $partnerContact->is_primary ? __('ui.pages.partner_contacts.index.values.primary') : __('ui.pages.partner_contacts.index.values.regular') }}
                                    </span>
                                    @if ($partnerContact->description)
                                        <span class="row-subtitle">{{ $partnerContact->description }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @can('edit partner contacts')
                                            <a class="action-pill" href="{{ route('partner-contacts.edit', $partnerContact) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcan

                                        @can('delete partner contacts')
                                            <form
                                                method="POST"
                                                action="{{ route('partner-contacts.destroy', $partnerContact) }}"
                                                data-confirm-message="{{ __('ui.pages.partner_contacts.index.confirm_delete') }}"
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
                    {{ __('ui.pages.partner_contacts.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$partnerContacts" />
        </div>
    </div>
@endsection

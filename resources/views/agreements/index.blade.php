@extends('layouts.dashboard')

@section('title', __('ui.sidebar.all_agreements'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.agreements')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.all_agreements') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.agreements.index.subtitle') }}</p>
            </div>

            @can('create agreements')
                <a class="btn btn--primary" href="{{ route('agreements.create') }}">
                    <i class="material-icons" aria-hidden="true">description</i>
                    <span>{{ __('ui.pages.agreements.index.create_action') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('agreements.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.agreements.index.search_label') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.agreements.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="country_id" aria-label="{{ __('ui.pages.agreements.index.country_filter') }}">
                <option value="">{{ __('ui.pages.agreements.index.all_countries') }}</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->id }}" @selected((string) $filters['country_id'] === (string) $country->id)>{{ $country->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="agreement_type_id" aria-label="{{ __('ui.pages.agreements.index.type_filter') }}">
                <option value="">{{ __('ui.pages.agreements.index.all_types') }}</option>
                @foreach ($agreementTypes as $agreementType)
                    <option value="{{ $agreementType->id }}" @selected((string) $filters['agreement_type_id'] === (string) $agreementType->id)>{{ $agreementType->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="agreement_direction_id" aria-label="{{ __('ui.pages.agreements.index.direction_filter') }}">
                <option value="">{{ __('ui.pages.agreements.index.all_directions') }}</option>
                @foreach ($agreementDirections as $agreementDirection)
                    <option value="{{ $agreementDirection->id }}" @selected((string) $filters['agreement_direction_id'] === (string) $agreementDirection->id)>{{ $agreementDirection->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="{{ __('ui.pages.agreements.index.status_filter') }}">
                <option value="">{{ __('ui.pages.agreements.index.all_statuses') }}</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter()->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('agreements.index') }}">
                    <i class="material-icons" aria-hidden="true">restart_alt</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($agreements->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.agreements.index.headers.agreement') }}</th>
                            <th>{{ __('ui.pages.agreements.index.headers.country_org') }}</th>
                            <th>{{ __('ui.pages.agreements.index.headers.type_direction') }}</th>
                            <th>{{ __('ui.pages.agreements.index.headers.duration') }}</th>
                            <th>{{ __('ui.pages.agreements.index.headers.responsible') }}</th>
                            <th>{{ __('ui.pages.agreements.index.headers.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agreements as $agreement)
                            @php
                                $statusClass = match ($agreement->status) {
                                    'active' => 'is-active',
                                    'completed' => 'is-completed',
                                    'expired' => 'is-planned',
                                    default => 'is-muted',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $agreement->display_title }}</span>
                                    <span class="row-subtitle">
                                        {{ $agreement->agreement_number ?: __('ui.pages.agreements.index.values.number_missing') }}
                                        {{ ' - ' }}
                                        {{ $agreement->title_ru }}{{ $agreement->title_cryl ? ' / '.$agreement->title_cryl : '' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->country?->display_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $agreement->partnerOrganization?->display_name ?: __('ui.pages.agreements.index.values.organization_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->agreementType?->display_name ?: __('ui.pages.agreements.index.values.type_missing') }}</span>
                                    <span class="row-subtitle">{{ $agreement->agreementDirection?->display_name ?: __('ui.pages.agreements.index.values.direction_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->signed_date?->format('d.m.Y') ?: __('ui.pages.agreements.index.values.signed_date_missing') }}</span>
                                    <span class="row-subtitle">
                                        {{ $agreement->start_date?->format('d.m.Y') ?: '--' }}
                                        {{ ' - ' }}
                                        {{ $agreement->end_date?->format('d.m.Y') ?: '--' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $agreement->responsibleUser?->full_name ?: __('ui.pages.agreements.index.values.responsible_missing') }}</span>
                                    <span class="row-subtitle">{{ $agreement->responsibleDepartment?->display_name ?: __('ui.pages.agreements.index.values.department_missing') }}</span>
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$agreement->status] ?? $agreement->status }}
                                    </span>
                                    @if ($agreement->description)
                                        <span class="row-subtitle">{{ $agreement->description }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        @canany(['edit agreements', 'edit own agreements'])
                                            <a class="action-pill" href="{{ route('agreements.edit', $agreement) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcanany

                                        @can('delete agreements')
                                            <form method="POST" action="{{ route('agreements.destroy', $agreement) }}" onsubmit="return confirm(@js(__('ui.pages.agreements.index.confirm_delete')));">
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
                    {{ __('ui.pages.agreements.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$agreements" />
        </div>
    </div>
@endsection

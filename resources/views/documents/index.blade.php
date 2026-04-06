@extends('layouts.dashboard')

@section('title', __('ui.sidebar.all_documents'))

@section('content')
    <div class="page-section">
        <div class="page-header">
            <div>
                <p class="eyebrow">{{ __('ui.common.eyebrows.crud', ['module' => __('ui.sidebar.documents')]) }}</p>
                <h1 class="page-title">{{ __('ui.sidebar.all_documents') }}</h1>
                <p class="page-subtitle">{{ __('ui.pages.documents.index.subtitle') }}</p>
            </div>

            @can('create documents')
                <a class="btn btn--primary" href="{{ route('documents.create') }}">
                    <i class="material-icons" aria-hidden="true">note_add</i>
                    <span>{{ __('ui.pages.documents.index.new') }}</span>
                </a>
            @endcan
        </div>

        <form class="toolbar" method="GET" action="{{ route('documents.index') }}">
            <label class="toolbar-search" aria-label="{{ __('ui.pages.documents.index.search_aria') }}">
                <i class="material-icons" aria-hidden="true">search</i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="{{ __('ui.pages.documents.index.search_placeholder') }}">
            </label>

            <select class="toolbar-select" name="document_type_id" aria-label="{{ __('ui.pages.documents.index.type_filter') }}">
                <option value="">{{ __('ui.pages.documents.index.all_types') }}</option>
                @foreach ($documentTypes as $documentType)
                    <option value="{{ $documentType->id }}" @selected((string) $filters['document_type_id'] === (string) $documentType->id)>{{ $documentType->display_name }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="status" aria-label="{{ __('ui.pages.documents.index.status_filter') }}">
                <option value="">{{ __('ui.pages.documents.index.all_statuses') }}</option>
                @foreach ($statuses as $statusValue => $statusLabel)
                    <option value="{{ $statusValue }}" @selected($filters['status'] === $statusValue)>{{ $statusLabel }}</option>
                @endforeach
            </select>

            <select class="toolbar-select" name="is_confidential" aria-label="{{ __('ui.pages.documents.index.confidential_filter') }}">
                <option value="">{{ __('ui.pages.documents.index.all_docs') }}</option>
                <option value="1" @selected($filters['is_confidential'] === '1')>{{ __('ui.pages.documents.index.only_confidential') }}</option>
                <option value="0" @selected($filters['is_confidential'] === '0')>{{ __('ui.pages.documents.index.only_public') }}</option>
            </select>

            <button class="btn btn--ghost" type="submit">
                <i class="material-icons" aria-hidden="true">filter_list</i>
                <span>{{ __('ui.common.actions.filter') }}</span>
            </button>

            @if (collect($filters)->filter(fn ($value) => $value !== '' && $value !== null)->isNotEmpty())
                <a class="btn btn--ghost" href="{{ route('documents.index') }}">
                    <i class="material-icons" aria-hidden="true">refresh</i>
                    <span>{{ __('ui.common.actions.clear') }}</span>
                </a>
            @endif
        </form>

        <div class="table-card">
            @if ($documents->count())
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('ui.pages.documents.index.headers.document') }}</th>
                            <th>{{ __('ui.pages.documents.index.headers.file') }}</th>
                            <th>{{ __('ui.pages.documents.index.headers.link') }}</th>
                            <th>{{ __('ui.pages.documents.index.headers.uploader') }}</th>
                            <th>{{ __('ui.pages.documents.index.headers.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                            @php
                                $statusClass = match ($document->status) {
                                    'faol' => 'is-active',
                                    'nazoratda' => 'is-planned',
                                    'arxivlangan' => 'is-completed',
                                    default => 'is-muted',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="row-title">{{ $document->display_title }}</span>
                                    @if ($document->title_ru)
                                        <span class="row-subtitle">{{ $document->title_ru }}</span>
                                    @endif
                                    <span class="row-subtitle">{{ $document->document_number ?: __('ui.pages.documents.index.number_missing') }}</span>
                                    <span class="row-subtitle">{{ $document->documentType?->display_name ?: __('ui.pages.documents.index.type_missing') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ \Illuminate\Support\Str::limit($document->file_name, 40) }}</span>
                                    <span class="row-subtitle">{{ strtoupper($document->file_ext ?: __('ui.pages.documents.index.ext_fallback')) }}{{ $document->file_size_human ? ' / '.$document->file_size_human : '' }}</span>
                                    <span class="row-subtitle">{{ $document->mime_type ?: __('ui.pages.documents.index.mime_unknown') }}</span>
                                </td>
                                <td>
                                    <span class="row-title">{{ $document->country?->display_name ?: __('ui.pages.documents.index.country_missing') }}</span>
                                    <span class="row-subtitle">{{ $document->partnerOrganization?->display_name ?: __('ui.pages.documents.index.org_missing') }}</span>
                                    @if ($document->agreement)
                                        <span class="row-subtitle">{{ __('ui.pages.documents.index.agreement_prefix') }}: {{ $document->agreement->display_title }}</span>
                                    @endif
                                    @if ($document->visit)
                                        <span class="row-subtitle">{{ __('ui.pages.documents.index.visit_prefix') }}: {{ $document->visit->display_title }}</span>
                                    @endif
                                    @if ($document->event)
                                        <span class="row-subtitle">{{ __('ui.pages.documents.index.event_prefix') }}: {{ $document->event->display_title }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="row-title">{{ $document->uploader?->full_name ?: '-' }}</span>
                                    <span class="row-subtitle">{{ $document->created_at?->format('d.m.Y H:i') }}</span>
                                    @if ($document->is_confidential)
                                        <span class="row-subtitle">{{ __('ui.pages.documents.index.confidential_badge') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statuses[$document->status] ?? $document->status }}
                                    </span>
                                    @if ($document->description)
                                        <span class="row-subtitle">{{ \Illuminate\Support\Str::limit($document->description, 100) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="action-pill" href="{{ route('documents.download', $document) }}">
                                            <i class="material-icons" aria-hidden="true">file_download</i>
                                            <span>{{ __('ui.pages.documents.index.download') }}</span>
                                        </a>

                                        @canany(['edit documents', 'edit own documents'])
                                            <a class="action-pill" href="{{ route('documents.edit', $document) }}">
                                                <i class="material-icons" aria-hidden="true">edit</i>
                                                <span>{{ __('ui.common.actions.edit') }}</span>
                                            </a>
                                        @endcanany

                                        @can('delete documents')
                                            <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm(@json(__('ui.pages.documents.index.confirm_delete')));">
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
                    {{ __('ui.pages.documents.index.empty') }}
                </div>
            @endif

            <x-dashboard-pagination :paginator="$documents" />
        </div>
    </div>
@endsection

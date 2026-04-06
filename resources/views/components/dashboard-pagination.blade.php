@props(['paginator'])

@if ($paginator->hasPages())
    <div class="pagination-shell">
        <div class="pagination-summary">
            {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} / {{ $paginator->total() }}
        </div>

        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="page-link is-disabled">{{ __('ui.common.pagination.previous') }}</span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}">{{ __('ui.common.pagination.previous') }}</a>
            @endif

            <span class="page-indicator">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}">{{ __('ui.common.pagination.next') }}</a>
            @else
                <span class="page-link is-disabled">{{ __('ui.common.pagination.next') }}</span>
            @endif
        </div>
    </div>
@endif

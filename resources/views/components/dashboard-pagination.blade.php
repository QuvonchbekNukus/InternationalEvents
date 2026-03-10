@props(['paginator'])

@if ($paginator->hasPages())
    <div class="pagination-shell">
        <div class="pagination-summary">
            {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} / {{ $paginator->total() }}
        </div>

        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="page-link is-disabled">Oldingi</span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}">Oldingi</a>
            @endif

            <span class="page-indicator">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}">Keyingi</a>
            @else
                <span class="page-link is-disabled">Keyingi</span>
            @endif
        </div>
    </div>
@endif

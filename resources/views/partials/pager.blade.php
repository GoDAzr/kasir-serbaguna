@if ($paginator->hasPages())
    <nav class="pager" aria-label="Halaman">
        @if ($paginator->onFirstPage())<span class="pg off">&laquo;</span>@else<a class="pg" href="{{ $paginator->previousPageUrl() }}">&laquo;</a>@endif
        <span class="pg-info">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())<a class="pg" href="{{ $paginator->nextPageUrl() }}">&raquo;</a>@else<span class="pg off">&raquo;</span>@endif
    </nav>
@endif

@props(['paginator', 'label' => 'Pagination'])

@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        if ($lastPage <= 5) {
            $pages = range(1, $lastPage);
        } elseif ($currentPage <= 3) {
            $pages = [...range(1, 4), 'ellipsis', $lastPage];
        } elseif ($currentPage >= $lastPage - 2) {
            $pages = [1, 'ellipsis', ...range($lastPage - 3, $lastPage)];
        } else {
            $pages = [1, 'ellipsis', $currentPage - 1, $currentPage, $currentPage + 1, 'ellipsis', $lastPage];
        }
    @endphp

    <nav class="admin-pagination" aria-label="{{ $label }}">
        @if ($paginator->onFirstPage())
            <span class="admin-pagination-button is-disabled" aria-disabled="true"><x-lucide-chevron-left aria-hidden="true" /></span>
        @else
            <a class="admin-pagination-button" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><x-lucide-chevron-left aria-hidden="true" /></a>
        @endif

        @foreach ($pages as $page)
            @if ($page === 'ellipsis')
                <span class="admin-pagination-ellipsis" aria-hidden="true">…</span>
            @elseif ($page === $currentPage)
                <span class="admin-pagination-button is-current" aria-current="page">{{ $page }}</span>
            @else
                <a class="admin-pagination-button" href="{{ $paginator->url($page) }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="admin-pagination-button" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><x-lucide-chevron-right aria-hidden="true" /></a>
        @else
            <span class="admin-pagination-button is-disabled" aria-disabled="true"><x-lucide-chevron-right aria-hidden="true" /></span>
        @endif
    </nav>
@endif
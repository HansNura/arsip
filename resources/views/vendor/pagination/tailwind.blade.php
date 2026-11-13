@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi halaman" class="pagination">
        @if ($paginator->onFirstPage())
            <span class="is-disabled">Sebelumnya</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="is-disabled" aria-hidden="true">{{ $element }}</span>
            @elseif (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>
        @else
            <span class="is-disabled">Berikutnya</span>
        @endif
    </nav>
@endif

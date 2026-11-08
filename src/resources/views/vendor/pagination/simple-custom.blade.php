@if ($paginator->hasPages())
<nav class="pagi" aria-label="Navigasi halaman">
    {{-- Prev --}}
    @if ($paginator->onFirstPage())
    <span class="disabled" aria-disabled="true">← Prev</span>
    @else
    <a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Prev</a>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
        @if (is_string($element))
            <span>{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
    <a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a>
    @else
    <span class="disabled" aria-disabled="true">Next →</span>
    @endif
</nav>
@endif

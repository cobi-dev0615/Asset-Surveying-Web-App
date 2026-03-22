@if ($paginator->hasPages())
    <div class="pagination">
        @if ($paginator->onFirstPage())
            <span style="opacity:0.5; cursor:default;">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="opacity:0.5; cursor:default;">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">&rsaquo;</a>
        @else
            <span style="opacity:0.5; cursor:default;">&rsaquo;</span>
        @endif
    </div>
@endif

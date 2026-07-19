@if ($paginator->hasPages())
    <div class="pagination-list">
        <ul class="list-inline">
            @if ($paginator->onFirstPage())
            <li><span><x-icon name="arrow-left" width="14" height="14" /></span></li>
            @else
            <li><a href="{{ $paginator->previousPageUrl() }}"><x-icon name="arrow-left" width="14" height="14" /></a></li>
            @endif
            @foreach ($elements as $element)
            @if (is_string($element))
            <li><span>{{ $element }}</li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                <li><span class="active">{{ $page }}</span></li>
                @else
                <li><a href="{{ $url }}">{{ $page }}</a></li>
                @endif
                @endforeach
            @endif
            @endforeach
            @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}"><x-icon name="arrow-right" width="14" height="14" /></a></li>
            @else
            <li><span><x-icon name="arrow-right" width="14" height="14" /></span></li>
            @endif
        </ul>
    </div>
@endif

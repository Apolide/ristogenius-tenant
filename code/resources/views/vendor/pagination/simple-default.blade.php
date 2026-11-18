@if ($paginator->hasPages())
    <nav>
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true"><span><i class="las la-angle-left" aria-hidden="true"></i> @lang('pagination.previous')</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="las la-angle-left" aria-hidden="true"></i> @lang('pagination.previous')</a></li>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next') <i class="las la-angle-right" aria-hidden="true"></i></a></li>
            @else
                <li class="disabled" aria-disabled="true"><span>@lang('pagination.next') <i class="las la-angle-right" aria-hidden="true"></i></span></li>
            @endif
        </ul>
    </nav>
@endif

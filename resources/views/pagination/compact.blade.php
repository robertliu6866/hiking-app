@if ($paginator->hasPages())
    <nav role="navigation" aria-label="行程換頁" class="flex items-center justify-between gap-2">
        @if ($paginator->onFirstPage())
            <span aria-disabled="true" class="ui-btn-ghost shrink-0 px-3 opacity-50">上一頁</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ui-btn-ghost shrink-0 px-3">上一頁</a>
        @endif

        <span aria-current="page" class="min-w-0 text-center text-sm text-slate-600">
            第 {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }} 頁
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ui-btn-soft shrink-0 px-3">下一頁</a>
        @else
            <span aria-disabled="true" class="ui-btn-ghost shrink-0 px-3 opacity-50">下一頁</span>
        @endif
    </nav>
@endif

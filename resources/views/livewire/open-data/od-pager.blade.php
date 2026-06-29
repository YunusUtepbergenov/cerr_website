@if ($paginator->hasPages())
    <nav class="od-pager" role="navigation" aria-label="{{ __('messages.open_data') }}">
        @if ($paginator->onFirstPage())
            <span class="od-page od-nav is-disabled" aria-disabled="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"></path></svg>
            </span>
        @else
            <button type="button" wire:click="previousPage" wire:loading.attr="disabled" class="od-page od-nav" rel="prev" aria-label="@lang('pagination.previous')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"></path></svg>
            </button>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="od-ellipsis">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="od-page is-current" aria-current="page">{{ $page }}</span>
                    @else
                        <button type="button" wire:click="gotoPage({{ $page }})" class="od-page">{{ $page }}</button>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage" wire:loading.attr="disabled" class="od-page od-nav" rel="next" aria-label="@lang('pagination.next')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"></path></svg>
            </button>
        @else
            <span class="od-page od-nav is-disabled" aria-disabled="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"></path></svg>
            </span>
        @endif
    </nav>
@endif

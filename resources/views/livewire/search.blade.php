<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
        <link rel="stylesheet" href="{{ asset('css/site-pages.css') }}?v={{ filemtime(public_path('css/site-pages.css')) }}">
    @endpush

    <section class="news-article-section">
        <div class="sp-wide">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <span class="current">@lang('messages.search')</span>
            </nav>

            <h1 class="article-title">@lang('messages.search')</h1>

            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass search-box-icon" aria-hidden="true"></i>
                <input type="search" class="search-box-input" wire:model.live.debounce.400ms="q"
                       placeholder="@lang('messages.search_placeholder')" aria-label="@lang('messages.search')" autofocus>
            </div>

            @if (mb_strlen($term) >= \App\Livewire\Search::MIN_QUERY_LENGTH)
                <p class="search-count">@lang('messages.search_found') <strong>{{ $totalCount }}</strong></p>

                @if ($results->isNotEmpty())
                    <div class="news-grid">
                        @foreach ($results as $item)
                            <a href="{{ route('show.news', $item->slug) }}" class="news-card" wire:key="search-news-{{ $item->id }}">
                                <span class="nc-thumb">
                                    @if ($item->translation?->coverUrl())
                                        <img src="{{ $item->translation->coverUrl() }}" alt="{{ $item->translation->title }}" loading="lazy">
                                    @endif
                                </span>
                                <span class="nc-body">
                                    <span class="nc-title">{{ $item->translation?->title }}</span>
                                    <span class="nc-meta">
                                        @if ($item->created_at)
                                            <span><i class="fa-light fa-clock"></i>{{ $item->created_at->format('d.m.Y') }}</span>
                                        @endif
                                    </span>
                                    @if ($item->translation?->short_description)
                                        <span class="nc-excerpt">{{ $item->translation->short_description }}</span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>

                    @if ($perPage < $totalCount)
                        <div class="sp-more-wrap">
                            <button type="button" wire:click.prevent="loadMore" class="sp-more-btn">
                                <i class="fa-solid fa-arrow-down"></i> @lang('messages.load_more')
                            </button>
                        </div>
                    @endif
                @else
                    <p class="search-empty">@lang('messages.nothing_found')</p>
                @endif
            @endif
        </div>
    </section>
</div>

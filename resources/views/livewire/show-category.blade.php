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
                <span class="current">{{ $category->translation->name }}</span>
            </nav>

            <h1 class="article-title">{{ $category->translation->name }}</h1>

            @if ($news->isNotEmpty())
                <div class="news-grid">
                    @foreach ($news as $item)
                        <a href="{{ route('show.news', $item->slug) }}" class="news-card" wire:key="cat-news-{{ $item->id }}">
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
                                    <span><i class="fa-light fa-eye"></i>{{ $item->view_count }}</span>
                                </span>
                                @if ($item->translation?->short_description)
                                    <span class="nc-excerpt">{{ $item->translation->short_description }}</span>
                                @endif
                            </span>
                        </a>
                    @endforeach
                </div>

                @if ($perPage < $totalCount)
                    <div class="sp-more-wrap" x-intersect.margin.300px="$wire.loadMore()">
                        <button type="button" wire:click.prevent="loadMore" class="sp-more-btn" wire:loading.attr="disabled">
                            <i class="fa-solid fa-arrow-down"></i> @lang('messages.load_more')
                        </button>
                    </div>
                @endif
            @endif
        </div>
    </section>

    @if ($popular_news->isNotEmpty())
        <section class="article-more">
            <div class="article-more-inner">
                <div class="article-more-block">
                    <h2 class="article-more-title">@lang('messages.popular')</h2>
                    <div class="popular-list">
                        @foreach ($popular_news as $item)
                            <a href="{{ route('show.news', $item->slug) }}" class="popular-item" wire:key="popular-{{ $item->id }}">
                                <span class="pop-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="pop-meta">
                                    <span class="pop-title">{{ $item->translation?->title }}</span>
                                    @if ($item->created_at)
                                        <span class="pop-date">{{ $item->created_at->format('d.m.Y') }}</span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
</div>

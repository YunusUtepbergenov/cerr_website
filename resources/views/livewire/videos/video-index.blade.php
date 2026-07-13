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
                <span class="current">@lang('messages.videogallery')</span>
            </nav>

            <h1 class="article-title">@lang('messages.videogallery')</h1>

            @if ($videos->isNotEmpty())
                <div class="video-grid" wire:ignore.self>
                    @foreach ($videos as $video)
                        <a href="{{ $video->url }}" class="video-card play-video popup-youtube" wire:key="video-{{ $video->id }}">
                            <span class="vc-thumb">
                                @if ($video->thumbnailUrl())
                                    <img src="{{ $video->thumbnailUrl() }}" alt="{{ $video->title }}" loading="lazy">
                                @endif
                                <span class="vc-play"><span><i class="fa-solid fa-play"></i></span></span>
                            </span>
                            <span class="vc-title">{{ $video->title }}</span>
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

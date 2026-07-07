<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}">
        <link rel="stylesheet" href="{{ asset('css/site-pages.css') }}">
    @endpush

    <section class="news-article-section">
        <article class="news-article">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <a href="{{ route('videos.index') }}">@lang('messages.videogallery')</a>
                <span class="sep">/</span>
                <span class="current">{{ $video->title }}</span>
            </nav>

            <h1 class="article-title">{{ $video->title }}</h1>

            <div class="article-meta">
                @if ($video->created_at)
                    <span>{{ $video->created_at->format('d.m.Y') }}</span>
                @endif
            </div>

            <a href="{{ $video->url }}" class="video-stage play-video popup-youtube" aria-label="{{ $video->title }}">
                @if ($video->thumbnailUrl())
                    <img src="{{ $video->thumbnailUrl() }}" alt="{{ $video->title }}">
                @endif
                <span class="vc-play"><span><i class="fa-solid fa-play"></i></span></span>
            </a>
        </article>
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

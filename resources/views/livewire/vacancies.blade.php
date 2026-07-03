<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}">
    @endpush

    <section class="news-article-section">
        <article class="news-article">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <span class="current">{{ $page->translation->title }}</span>
            </nav>

            <h1 class="article-title">{{ $page->translation->title }}</h1>

            @if (str_starts_with((string) $page->translation->image, 'http'))
                <figure class="article-cover">
                    <img src="{{ $page->translation->image }}" alt="{{ $page->translation->title }}">
                </figure>
            @endif

            <div class="news-article-body">
                @sanitized($page->translation->content)
            </div>
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

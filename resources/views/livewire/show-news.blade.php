<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}?v={{ filemtime(public_path('css/news-article.css')) }}">
    @endpush

    @php($views = (int) $news->view_count)

    <section class="news-article-section">
        <article class="news-article">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                @if ($news->category?->translation)
                    <span class="sep">/</span>
                    <a href="{{ route('show.category', $news->category->slug) }}">{{ $news->category->translation->name }}</a>
                @endif
                <span class="sep">/</span>
                <span class="current">{{ $news->translation->title }}</span>
            </nav>

            @if ($news->category?->translation)
                <a href="{{ route('show.category', $news->category->slug) }}" class="article-kicker">{{ $news->category->translation->name }}</a>
            @endif

            <h1 class="article-title">{{ $news->translation->title }}</h1>

            @if ($news->translation->short_description)
                <p class="article-lead">{{ $news->translation->short_description }}</p>
            @endif

            <div class="article-meta">
                @if ($news->created_at)
                    <span>{{ $news->created_at->format('d.m.Y') }}</span>
                @endif
                <span>{{ $views >= 1000 ? round($views / 1000, 1).'k' : $views }} @lang('messages.views_label')</span>
                <span>{{ $news->translation->readingTime() }} @lang('messages.min_read')</span>
            </div>

            @if ($news->translation->coverUrl())
                <figure class="article-cover">
                    <img src="{{ $news->translation->coverUrl() }}" alt="{{ $news->translation->title }}">
                </figure>
            @endif

            <div class="news-article-body">
                @sanitized($news->translation->content)
            </div>

            @if ($news->tags->isNotEmpty())
                <div class="article-footer">
                    <div class="article-tags">
                        @foreach ($news->tags as $tag)
                            <span class="article-tag">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </article>
    </section>

    @if ($related_news->isNotEmpty() || $popular_news->isNotEmpty())
        <section class="article-more">
            <div class="article-more-inner">
                @if ($related_news->isNotEmpty())
                    <div class="article-more-block">
                        <h2 class="article-more-title">@lang('messages.related_news')</h2>
                        <div class="related-grid">
                            @foreach ($related_news as $item)
                                <a href="{{ route('show.news', $item->slug) }}" class="related-card" wire:key="related-{{ $item->id }}">
                                    <span class="rc-thumb">
                                        @if ($item->translation?->coverUrl())
                                            <img src="{{ $item->translation->coverUrl() }}" alt="{{ $item->translation->title }}">
                                        @endif
                                    </span>
                                    <span class="rc-body">
                                        <span class="rc-title">{{ $item->translation?->title }}</span>
                                        @if ($item->created_at)
                                            <span class="rc-date">{{ $item->created_at->format('d.m.Y') }}</span>
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($popular_news->isNotEmpty())
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
                @endif
            </div>
        </section>
    @endif
</div>

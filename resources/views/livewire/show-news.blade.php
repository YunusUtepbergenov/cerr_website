<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}">
    @endpush

    @php($views = (int) $news->view_count)

    <section class="echo-hero-section inner inner-post">
        <div class="container">
            <div class="row gx-5 sticky-coloum-wrap">
                <div class="col-xl-8 col-lg-8">
                    <article class="news-article">
                        <nav class="article-breadcrumb" aria-label="breadcrumb">
                            <a href="{{ route('home') }}">@lang('messages.main')</a>
                            @if ($news->category?->translation)
                                <span class="sep">/</span>
                                <a href="{{ route('show.category', $news->category->slug) }}">{{ $news->category->translation->name }}</a>
                            @endif
                        </nav>

                        @if ($news->category?->translation)
                            <a href="{{ route('show.category', $news->category->slug) }}" class="article-category-badge">{{ $news->category->translation->name }}</a>
                        @endif

                        <h1 class="article-title">{{ $news->translation->title }}</h1>

                        <div class="article-meta">
                            <span><i class="fa-light fa-clock"></i> {{ $news->created_at->format('d.m.Y') }}</span>
                            <span><i class="fa-light fa-eye"></i> {{ $views >= 1000 ? round($views / 1000, 1).'k' : $views }} @lang('messages.views_label')</span>
                            <span><i class="fa-light fa-book-open"></i> {{ $news->translation->readingTime() }} @lang('messages.min_read')</span>
                        </div>

                        @if ($news->translation->short_description)
                            <p class="article-lead">{{ $news->translation->short_description }}</p>
                        @endif

                        @if ($news->translation->coverUrl())
                            <figure class="article-cover">
                                <img src="{{ $news->translation->coverUrl() }}" alt="{{ $news->translation->title }}">
                            </figure>
                        @endif

                        <div class="news-article-body">
                            @sanitized($news->translation->content)
                        </div>

                        <div class="article-footer">
                            @if ($news->tags->isNotEmpty())
                                <div class="article-tags">
                                    @foreach ($news->tags as $tag)
                                        <span class="article-tag">#{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span></span>
                            @endif

                            <div class="article-share" x-data="articleShare(@js(route('show.news', $news->slug)))">
                                <span class="share-label">@lang('messages.share'):</span>
                                <a :href="telegram" target="_blank" rel="noopener" class="share-btn"><i class="fa-brands fa-telegram"></i></a>
                                <a :href="facebook" target="_blank" rel="noopener" class="share-btn"><i class="fa-brands fa-facebook-f"></i></a>
                                <a :href="twitter" target="_blank" rel="noopener" class="share-btn"><i class="fa-brands fa-x-twitter"></i></a>
                                <button type="button" @click="copy()" class="share-btn" title="@lang('messages.copy_link')"><i class="fa-light fa-link"></i></button>
                                <span class="copied" x-show="copied" x-cloak>@lang('messages.link_copied')</span>
                            </div>
                        </div>
                    </article>

                    @if ($related_news->isNotEmpty())
                        <section class="related-news">
                            <h3 class="related-title">@lang('messages.related_news')</h3>
                            <div class="row">
                                @foreach ($related_news as $item)
                                    <div class="col-md-4" wire:key="related-{{ $item->id }}">
                                        <a href="{{ route('show.news', $item->slug) }}" class="related-card">
                                            <div class="related-thumb">
                                                @if ($item->translation?->coverUrl())
                                                    <img src="{{ $item->translation->coverUrl() }}" alt="{{ $item->translation->title }}">
                                                @endif
                                            </div>
                                            <h4 class="related-card-title">{{ $item->translation?->title }}</h4>
                                            <span class="related-card-date">{{ $item->created_at->format('d.m.Y') }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <div class="col-xl-4 col-lg-4 sticky-coloum-item">
                    <div class="echo-right-ct-1">
                        <div class="echo-home-1-hero-area-top-story">
                            <h5 class="text-center">@lang('messages.popular')</h5>
                            @foreach ($popular_news as $item)
                                <div class="echo-top-story" wire:key="popular-{{ $item->id }}">
                                    <div class="echo-story-picture img-transition-scale">
                                        <a href="{{ route('show.news', $item->slug) }}"><img src="{{ $item->translation->coverUrl() }}" alt="{{ $item->translation->title }}" class="img-hover"></a>
                                    </div>
                                    <div class="echo-story-text">
                                        <h6><a href="{{ route('show.news', $item->slug) }}" class="title-hover">{{ $item->translation->title }}</a></h6>
                                        <a href="{{ route('show.news', $item->slug) }}" class="pe-none"><i class="fa-light fa-clock"></i> {{ $item->created_at->format('d-m-Y') }}</a>
                                    </div>
                                </div>
                                <hr>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('articleShare', (url) => ({
                    copied: false,
                    get encoded() { return encodeURIComponent(url); },
                    get telegram() { return 'https://t.me/share/url?url=' + this.encoded; },
                    get facebook() { return 'https://www.facebook.com/sharer/sharer.php?u=' + this.encoded; },
                    get twitter() { return 'https://twitter.com/intent/tweet?url=' + this.encoded; },
                    copy() {
                        navigator.clipboard.writeText(url).then(() => {
                            this.copied = true;
                            setTimeout(() => { this.copied = false; }, 2000);
                        });
                    },
                }));
            });
        </script>
    @endpush
</div>

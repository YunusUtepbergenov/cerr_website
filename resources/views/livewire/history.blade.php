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

            <div class="news-article-body">
                @sanitized($page->translation->content)
            </div>
        </article>
    </section>
</div>

<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}">
        <link rel="stylesheet" href="{{ asset('css/site-pages.css') }}">
    @endpush

    <section class="news-article-section">
        <div class="sp-wide">
            <nav class="article-breadcrumb" aria-label="breadcrumb">
                <a href="{{ route('home') }}">@lang('messages.main')</a>
                <span class="sep">/</span>
                <span class="current">@lang('messages.leadership')</span>
            </nav>

            <h1 class="article-title">@lang('messages.leadership')</h1>

            <div class="leaders-grid">
                <div class="leader-card">
                    <img class="ld-photo" src="{{ asset('images/leadership/obidarzikulovich.jpg') }}" alt="@lang('leadership.obid_arzikulovich')">
                    <div class="ld-body">
                        <div class="ld-name">@lang('leadership.obid_arzikulovich')</div>
                        <div class="ld-role">@lang('leadership.director')</div>
                        <div class="ld-contacts">
                            <span><i class="fa-solid fa-phone"></i><a href="tel:+998781500202">+998 78 150-02-02</a></span>
                            <span><i class="fa-solid fa-envelope"></i><a href="mailto:o.khakimov@cerr.uz">o.khakimov@cerr.uz</a></span>
                        </div>
                    </div>
                </div>

                <div class="leader-card">
                    <img class="ld-photo" src="{{ asset('images/leadership/asadov.JPG') }}" alt="@lang('leadership.khurshed_saadullaevich')">
                    <div class="ld-body">
                        <div class="ld-name">@lang('leadership.khurshed_saadullaevich')</div>
                        <div class="ld-role">@lang('leadership.deputy')</div>
                        <div class="ld-contacts">
                            <span><i class="fa-solid fa-phone"></i><a href="tel:+998781500202">+998 78 150-02-02</a></span>
                            <span><i class="fa-solid fa-envelope"></i><a href="mailto:info@cerr.uz">info@cerr.uz</a></span>
                        </div>
                    </div>
                </div>

                <div class="leader-card">
                    <img class="ld-photo" src="{{ asset('images/leadership/ortiqov.jpg') }}" alt="@lang('leadership.nozimjon_kozimjonovich')">
                    <div class="ld-body">
                        <div class="ld-name">@lang('leadership.nozimjon_kozimjonovich')</div>
                        <div class="ld-role">@lang('leadership.deputy')</div>
                        <div class="ld-contacts">
                            <span><i class="fa-solid fa-phone"></i><a href="tel:+998781500202">+998 78 150-02-02</a></span>
                            <span><i class="fa-solid fa-envelope"></i><a href="mailto:info@cerr.uz">info@cerr.uz</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

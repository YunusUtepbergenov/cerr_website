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
                <span class="current">@lang('messages.contacts')</span>
            </nav>

            <h1 class="article-title">@lang('messages.contacts')</h1>

            <div class="contact-layout">
                <div class="contact-cards">
                    <div class="contact-card">
                        <span class="cc-ic"><i class="fa-solid fa-phone"></i></span>
                        <span class="cc-info">
                            <span class="cc-lbl">@lang('contact.st_phone')</span>
                            <span class="cc-val"><a href="tel:+998781500202">+998 78 150-02-02 (425, 426)</a></span>
                        </span>
                    </div>

                    <div class="contact-card">
                        <span class="cc-ic"><i class="fa-solid fa-envelope"></i></span>
                        <span class="cc-info">
                            <span class="cc-lbl">@lang('contact.st_email')</span>
                            <span class="cc-val"><a href="mailto:info@cerr.uz">info@cerr.uz</a></span>
                        </span>
                    </div>

                    <div class="contact-card">
                        <span class="cc-ic"><i class="fa-solid fa-location-dot"></i></span>
                        <span class="cc-info">
                            <span class="cc-lbl">@lang('contact.st_address')</span>
                            <span class="cc-val">@lang('messages.address')</span>
                        </span>
                    </div>

                    <div class="contact-card">
                        <span class="cc-ic"><i class="fa-solid fa-clock"></i></span>
                        <span class="cc-info">
                            <span class="cc-lbl">@lang('contact.st_work_hours')</span>
                            <span class="cc-val">@lang('contact.working_hours')</span>
                        </span>
                    </div>

                    <div class="contact-card">
                        <span class="cc-ic"><i class="fa-solid fa-share-nodes"></i></span>
                        <span class="cc-info">
                            <span class="cc-lbl">@lang('contact.st_social')</span>
                            <span class="cc-social">
                                <a href="https://www.facebook.com/CERR.Uzbekistan" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                                <a href="https://t.me/cerruz" target="_blank" rel="noopener" aria-label="Telegram"><i class="fa-brands fa-telegram"></i></a>
                                <a href="https://www.instagram.com/cerr.uz/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                                <a href="https://www.youtube.com/@centerforeconomicresearcha1331/featured" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                            </span>
                        </span>
                    </div>
                </div>

                <div class="contact-map">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2520.5429760767647!2d69.23362761234878!3d41.29912449263741!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38ae8b009c445977%3A0xe6bb28ea5ab63a4c!2z0KbQtdC90YLRgCDRjdC60L7QvdC-0LzQuNGH0LXRgdC60LjRhSDQuNGB0YHQu9C10LTQvtCy0LDQvdC40Lkg0Lgg0YDQtdGE0L7RgNC8!5e0!3m2!1sru!2s!4v1742034411089!5m2!1sru!2s"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="CERR location map"></iframe>
                </div>
            </div>
        </div>
    </section>
</div>

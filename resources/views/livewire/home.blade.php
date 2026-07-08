<div>
    <section class="custom-hero-section py-5">
        <div class="container">
            <div class="position-relative overflow-hidden custom-hero-inner p-5 d-flex justify-content-center align-items-center">
    
                <img src="{{asset('images/main_image/hero.png')}}" alt="Decorative background"
                     class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover z-n1" />
    
                <div class="text-center position-relative z-1">
                    <h1 class="display-4 fw-bold hero-title text-white mb-4">@lang('messages.org_name')</h1>
                </div>
    
            </div>
        </div>
    </section>

    <section class="echo-latest-news-area" data-aos="fade-left" data-aos-duration="600" data-aos-delay="100">
        <div class="echo-latest-news-content">
            <div class="container">
                <div class="echo-be-slider-btn">
                    <div class="echo-latest-nw-title">
                        <h4 lang="{{ app()->getLocale() }}">@lang('messages.our_research')</h4>
                    </div>
                    <div class="echo-latest-news-next-prev-btn">
                        <div class="swiper-button-next" id="researchNext"></div>
                        <div class="swiper-button-prev" id="researchPrev"></div>
                    </div>
                </div>

                <div class="echo-latest-news-full-content">
                    <div class="swiper" id="research">
                        <div class="swiper-wrapper">
                            @foreach ($latest_news as $news)
                                <div class="swiper-slide" wire:key="research-{{ $news->id }}">
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}">
                                                <img src="{{$news->translation->coverUrl()}}" alt="Echo" class="img-hover" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="echo-latest-news-single-title mt-4">
                                            <h6 lang="{{ app()->getLocale() }}"><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                        </div>
                                        <div class="echo-latest-news-time-views">
                                            <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> {{ \Carbon\Carbon::parse($news->created_at)->format('d-m-Y') }}</a>
                                            <a href="#" class="pe-none"><i class="fa-light fa-eye"></i> {{$news->view_count}}</a>
                                        </div>
                                    </div>
                                </div>                                
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="echo-latest-news-area">
        <div class="echo-latest-news-content">
            <div class="container">
                <div class="echo-be-slider-btn">
                    <div class="echo-latest-nw-title">
                        <h4>@lang('messages.events')</h4>
                    </div>
                    <div class="echo-latest-news-next-prev-btn">
                        <div class="swiper-button-next eventsNext"></div>
                        <div class="swiper-button-prev eventsPrev"></div>
                    </div>
                </div>

                <div class="echo-latest-news-full-content">
                    <div class="swiper" id="eventsSwiper">
                        <div class="swiper-wrapper">
                            @foreach ($events as $news)
                                <div class="swiper-slide" wire:key="event-{{ $news->id }}">
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}">
                                                <img src="{{$news->translation->coverUrl()}}" alt="Echo" class="img-hover" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="echo-latest-news-single-title mt-4">
                                            <h6><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                        </div>
                                        <div class="echo-latest-news-time-views">
                                            <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> {{ \Carbon\Carbon::parse($news->created_at)->format('d-m-Y') }}</a>
                                            <a href="#" class="pe-none"><i class="fa-light fa-eye"></i> {{$news->view_count}}</a>
                                        </div>
                                    </div>
                                </div>                                
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="echo-latest-news-area">
        <div class="echo-latest-news-content">
            <div class="container">
                <div class="echo-be-slider-btn">
                    <div class="echo-latest-nw-title">
                        <h4>@lang('messages.infographics')</h4>
                    </div>
                    <div class="echo-latest-news-next-prev-btn">
                        <div class="swiper-button-next" id="infoNext"></div>
                        <div class="swiper-button-prev" id="infoPrev"></div>
                    </div>
                </div>

                <div class="echo-latest-news-full-content">
                    <div class="swiper" id="infoSwiper">
                        <div class="swiper-wrapper">
                            @foreach ($infographics as $news)
                                <div class="swiper-slide" wire:key="infographic-{{ $news->id }}">
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}">
                                                <img src="{{$news->translation->coverUrl()}}" alt="Echo" class="img-hover" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="echo-latest-news-single-title mt-4">
                                            <h6><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                        </div>
                                        <div class="echo-latest-news-time-views">
                                            <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> {{ \Carbon\Carbon::parse($news->created_at)->format('d-m-Y') }}</a>
                                            <a href="#" class="pe-none"><i class="fa-light fa-eye"></i> {{$news->view_count}}</a>
                                        </div>
                                    </div>
                                </div>                                
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <br>

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}">
        <link rel="stylesheet" href="{{ asset('css/site-pages.css') }}">
    @endpush

    @if ($videos->isNotEmpty())
        <section class="home-videos">
            <div class="hv-inner">
                <div class="hv-head">
                    <h2 class="hv-title">@lang('messages.videogallery')</h2>
                    <a href="{{ route('videos.index') }}" class="hv-all">@lang('messages.show_more') <i class="fa-solid fa-arrow-right hv-all-icon"></i></a>
                </div>

                @php($featured = $videos->first())
                <div class="hv-stage">
                    <a href="{{ $featured->url }}" class="hv-feature play-video popup-youtube" wire:key="hv-feature-{{ $featured->id }}">
                        <span class="hv-feature-thumb">
                            @if ($featured->thumbnailUrl())
                                <img class="hv-img" src="{{ $featured->thumbnailUrl() }}" alt="{{ $featured->title }}">
                            @endif
                            <span class="hv-veil"></span>
                            <span class="hv-disc"><i class="fa-solid fa-play hv-disc-icon"></i></span>
                        </span>
                        <span class="hv-feature-meta">
                            <span class="hv-feature-title">{{ $featured->title }}</span>
                            @if ($featured->created_at)
                                <span class="hv-date">{{ $featured->created_at->format('d.m.Y') }}</span>
                            @endif
                        </span>
                    </a>

                    @if ($videos->count() > 1)
                        <div class="hv-list">
                            @foreach ($videos->skip(1) as $video)
                                <a href="{{ $video->url }}" class="hv-row play-video popup-youtube" wire:key="hv-row-{{ $video->id }}">
                                    <span class="hv-row-thumb">
                                        @if ($video->thumbnailUrl())
                                            <img class="hv-img" src="{{ $video->thumbnailUrl() }}" alt="{{ $video->title }}" loading="lazy">
                                        @endif
                                        <span class="hv-veil"></span>
                                        <span class="hv-disc hv-disc-sm"><i class="fa-solid fa-play hv-disc-icon"></i></span>
                                    </span>
                                    <span class="hv-row-body">
                                        <span class="hv-row-title">{{ $video->title }}</span>
                                        @if ($video->created_at)
                                            <span class="hv-date">{{ $video->created_at->format('d.m.Y') }}</span>
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif


    @php($journals = [
        ['href' => 'https://review.uz/journals/view/8-44-2025', 'cover' => 'https://static.review.uz/crop/1/8/200_265_95_1879944794.jpg?v=1757314135'],
        ['href' => 'https://review.uz/journals/view/8-307-2025', 'cover' => 'https://static.review.uz/crop/1/4/200_265_95_1409325190.jpg?v=1756985379'],
        ['href' => 'https://review.uz/journals/view/1-08-2025', 'cover' => 'https://static.review.uz/crop/9/5/200_265_95_958738944.jpg?v=1756189246'],
        ['href' => 'https://review.uz/journals/view/7-43-2025', 'cover' => 'https://static.review.uz/crop/1/4/360__95_1417567118.jpg?v=1755058724'],
    ])
    <section class="home-journals" aria-labelledby="hj-title">
        <div class="hs-inner">
            <div class="hs-head">
                <h2 class="hs-title" id="hj-title">@lang('messages.journals')</h2>
                <a class="hs-more" href="https://review.uz/journals" target="_blank" rel="noopener noreferrer">review.uz <i class="fa-solid fa-arrow-right hs-more-icon" aria-hidden="true"></i></a>
            </div>
            <div class="hj-grid">
                @foreach ($journals as $issue)
                    <a class="hj-item" href="{{ $issue['href'] }}" target="_blank" rel="noopener noreferrer">
                        <span class="hj-cover"><img class="hj-img" src="{{ $issue['cover'] }}" alt="Iqtisodiy sharh — {{ $loop->iteration }}" width="200" height="265" loading="lazy"></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @php($partners = [
        ['name' => 'UNDP', 'href' => 'https://www.undp.org', 'logo' => 'images/partners/undp.svg', 'w' => 237, 'h' => 482],
        ['name' => 'J-PAL', 'href' => 'https://www.povertyactionlab.org', 'logo' => 'images/partners/jpal.png', 'w' => 500, 'h' => 200],
        ['name' => 'CAERC Azerbaijan', 'href' => 'https://ereforms.gov.az', 'logo' => 'images/partners/iitkm.png', 'w' => 350, 'h' => 91],
        ['name' => 'German Economic Team', 'href' => 'https://www.german-economic-team.com', 'logo' => 'images/partners/get.svg', 'w' => 220, 'h' => 58],
        ['name' => 'IDDRI', 'href' => 'https://www.iddri.org', 'logo' => 'images/partners/iddri.svg', 'w' => 117, 'h' => 34],
        ['name' => 'ANKASAM', 'href' => 'https://www.ankasam.org', 'logo' => 'images/partners/ankasam.png', 'w' => 544, 'h' => 181],
        ['name' => 'Organization of Turkic States', 'href' => 'https://turkicstates.org', 'logo' => 'images/partners/ots.svg', 'w' => 178, 'h' => 178],
        ['name' => 'Eurasian Research Institute', 'href' => 'https://www.eurasian-research.org', 'logo' => 'images/partners/eri.png', 'w' => 385, 'h' => 216],
    ])
    <section class="home-partners" aria-labelledby="hp-title">
        <div class="hs-inner hp-inner">
            <div class="hs-head">
                <h2 class="hs-title" id="hp-title">@lang('messages.our_partners')</h2>
            </div>
            <div class="hp-wall">
                @foreach ($partners as $partner)
                    <a class="hp-cell" href="{{ $partner['href'] }}" target="_blank" rel="noopener noreferrer">
                        <span class="hp-box"><img class="hp-logo" src="{{ asset($partner['logo']) }}" alt="{{ $partner['name'] }}" width="{{ $partner['w'] }}" height="{{ $partner['h'] }}" loading="lazy"></span>
                        <span class="hp-name">{{ $partner['name'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</div>



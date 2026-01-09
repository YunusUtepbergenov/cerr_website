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
                                                <img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover" loading="lazy">
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
                                                <img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover" loading="lazy">
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
                                                <img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover" loading="lazy">
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

    <section class="echo-video-area">
        <div class="echo-video-content">
            <div class="container">
                <div class="echo-video-area-title-row text-center">
                    <h6>@lang('messages.videogallery')</h6>
                </div>

                <div class="echo-full-video-content">
                    <div class="row gx-6">

                        <div class="col-xl-8 col-lg-8 col-md-12">
                            @if($videos->first())
                                <div class="echo-video-left-site">
                                    <a href="{{ $videos->first()->url }}" class="play-video popup-youtube">
                                        <img src="{{asset('images/video/'.$videos->first()->image)}}" alt="Video">
                                    </a>

                                    <div class="vedio-icone">
                                        <a class="play-video popup-youtube video-play-button" href="{{ $videos->first()->url }}">
                                            <span></span>
                                        </a>
                                        <div class="video-overlay">
                                            <a class="video-overlay-close">×</a>
                                        </div>
                                    </div>

                                    <div class="echo-video-left-site-text-box">
                                        <h5>
                                            <a href="{{ $videos->first()->url }}" class="play-video popup-youtube title-hover">
                                                {{ $videos->first()->title }}
                                            </a>
                                            <div class="video-overlay">
                                                <a class="video-overlay-close">×</a>
                                            </div>
                                        </h5>
                                        <hr>
                                        <div class="echo-video-left-site-read-views">
                                            <a href="#" class="pe-none">
                                                <i class="fa-light fa-clock"></i> {{ $videos->first()->created_at->format('d.m.Y') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-12">
                            <div class="echo-video-area-home-1-right-content-responsive">
                                @foreach($videos->skip(1) as $video)
                                    <div class="echo-video-right-site-content" wire:key="video-{{ $video->id }}">
                                        <div class="echo-video-right-site-content-text">
                                            <h5>
                                                <a href="{{ $video->url }}" class="play-video popup-youtube title-hover text-white">
                                                    {{ $video->title }}
                                                </a>
                                            </h5>
                                            <hr>
                                        </div>
                                        <div class="echo-video-right-site-content-video">
                                            <a href="{{ $video->url }}" class="play-video popup-youtube">
                                                <img src="{{asset('images/video/'.$video->image)}}" alt="Video">
                                            </a>
                                            <div class="vedio-icone">
                                                <a class="play-video popup-youtube video-play-button" href="{{ $video->url }}">
                                                    <span></span>
                                                </a>
                                                <div class="video-overlay">
                                                    <a class="video-overlay-close">×</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>


    <section class="echo-popular-news-area">
        <div class="echo-popular-news-area-content">
            <div class="container">
                <div class="echo-popular-news-area-full-content">
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12">
                            <div class="echo-popular-area-title">
                                <h4 class="text-center">Журналы</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row gx-5">
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/8-44-2025" target="_blank"><img src="https://static.review.uz/crop/1/8/200_265_95_1879944794.jpg?v=1757314135" alt="Echo" class="img-hover"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/8-307-2025" target="_blank"><img src="https://static.review.uz/crop/1/4/200_265_95_1409325190.jpg?v=1756985379" alt="Echo" class="img-hover"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item echo-popular-news-responsive-home-1">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/1-08-2025" target="_blank"><img src="https://static.review.uz/crop/9/5/200_265_95_958738944.jpg?v=1756189246" alt="Echo" class="img-hover"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item echo-popular-news-responsive-home-1">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/7-43-2025" target="_blank"><img src="https://static.review.uz/crop/1/4/360__95_1417567118.jpg?v=1755058724" alt="Echo" class="img-hover"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 mb-20 bg-light">
        <div class="container">
          <h2 class="fw-bold text-center mb-4">Bizning hamkorlarimiz</h2>
      
          <div id="partnersCarousel" class="carousel slide mt-40" data-bs-ride="carousel">
            <div class="carousel-inner">
              <!-- Slide 1 -->
              <div class="carousel-item active">
                <div class="d-flex justify-content-center gap-4 flex-wrap">
                  <!-- Partner 1 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://www.undp.org/sites/g/files/zskgke326/files/2025-04/undp-logo-blue.4f32e17f.svg" class="img-fluid mb-2" style="max-height: 150px;" alt="British Council">
                  </div>
                  <!-- Partner 2 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://upload.wikimedia.org/wikipedia/en/a/ab/J-PAL_Logo.png" class="img-fluid mb-2" style="max-height: 150px;" alt="Oxford">
                  </div>
                  <!-- Partner 3 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://ereforms.gov.az/image/iitkm_logo_en.png" class="img-fluid mb-2" style="max-height: 150px;" alt="UNDP">
                  </div>
                  <!-- Partner 4 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://www.german-economic-team.com/wp-content/uploads/2021/09/logo-get.svg" class="img-fluid mb-2" style="max-height: 150px;" alt="Glasgow">
                  </div>
                </div>
              </div>
      
              <!-- Slide 2 -->
              <div class="carousel-item">
                <div class="d-flex justify-content-center gap-4 flex-wrap">
                  <!-- Partner 5 -->
                  <div class="text-center" style="width: 200px;">
                    <img src="https://www.iddri.org/themes/custom/iddri/images/logo.svg" class="img-fluid mb-2" style="max-height: 100px;" alt="Partner 5">
                  </div>
                  <!-- Partner 6 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://www.ankasam.org/wp-content/uploads/2021/09/LOGO-THEMEPANEL-RETINA.png" class="img-fluid mb-2" style="max-height: 100px;" alt="Partner 6">
                  </div>
                  <!-- Partner 7 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a7/Emblem_of_the_Organization_of_Turkic_States.svg/250px-Emblem_of_the_Organization_of_Turkic_States.svg.png" class="img-fluid mb-2" style="max-height: 100px;" alt="Partner 7">
                  </div>
                  <!-- Partner 8 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://www.eurasian-research.org/wp-content/uploads/2023/06/ERI_logo.png" class="img-fluid mb-2" style="max-height: 100px;" alt="Partner 8">
                  </div>
                </div>
              </div>
            </div>
      
            <!-- Carousel controls -->
            <button class="carousel-control-prev" type="button" data-bs-target="#partnersCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#partnersCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon"></span>
            </button>
          </div>
        </div>
      </section>
</div>



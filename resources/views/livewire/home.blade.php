<div>
    <section class="custom-hero-section py-5">
        <div class="container">
            <div class="position-relative overflow-hidden custom-hero-inner p-5 d-flex justify-content-center align-items-center">
    
                <!-- Background Image -->
                <img src="{{Vite::asset('resources/images/main_image/hero.png')}}" alt="Decorative background"
                     class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover z-n1" />
    
                <!-- Centered Text Content -->
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
                        <h4>@lang('messages.our_research')</h4>
                    </div>
                    <div class="echo-latest-news-next-prev-btn">
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>

                <div class="echo-latest-news-full-content">
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($latest_news as $news)
                                <div class="swiper-slide" wire:key={{$news->id}}>
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}">
                                                <img src="{{Vite::asset('resources/images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover">
                                            </a>
                                        </div>
                                        <div class="echo-latest-news-single-title mt-5">
                                            <h6><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                        </div>
                                        <div class="echo-latest-news-time-views">
                                            <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
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
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>

                <div class="echo-latest-news-full-content">
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($events as $news)
                                <div class="swiper-slide" wire:key={{$news->id}}>
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}">
                                                <img src="{{Vite::asset('resources/images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover">
                                            </a>
                                        </div>
                                        <div class="echo-latest-news-single-title mt-5">
                                            <h6><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                        </div>
                                        <div class="echo-latest-news-time-views">
                                            <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
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
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>

                <div class="echo-latest-news-full-content">
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($infographics as $news)
                                <div class="swiper-slide" wire:key={{$news->id}}>
                                    <div class="echo-latest-news-main-content">
                                        <div class="echo-latest-news-img img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}">
                                                <img src="{{Vite::asset('resources/images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover">
                                            </a>
                                        </div>
                                        <div class="echo-latest-news-single-title mt-5">
                                            <h6><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                        </div>
                                        <div class="echo-latest-news-time-views">
                                            <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
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
{{-- 
    <section class="echo-de-category-area">
        <div class="echo-de-category-area-content">
            <div class="container">
                <div class="echo-de-category-full-content">
                    <div class="echo-de-category-title-btn">
                        <h4 class="text-capitalize">@lang('messages.categories')</h4>
                        <a wire:navigate href="{{route('show.all.category')}}" class="text-capitalize echo-py-btn">@lang('messages.all_categories')</a>
                    </div>
                    <div class="row gx-5">
                        @foreach ($categories as $category)
                            <div class="col-xl-4 col-lg-4 col-md-6">
                                <div class="echo-de-category-content echo-responsive-wd">
                                    <h5 class="text-capitalize">{{$category->translation->name}}</h5>
                                    <hr style="background-color: currentcolor;">
                                    @foreach ($category->getLatestNews as $item)
                                        <div class="echo-de-category-content-img-title">
                                            <div class="echo-de-category-content-img img-transition-scale">
                                                <a wire:navigate href="{{route('show.news', $item->slug)}}">
                                                    <img src="{{Vite::asset('resources/images/news/'.$item->translation->image_url)}}" alt="Echo" class="img-hover">
                                                </a>
                                            </div>
                                            <div class="echo-de-category-content-title">
                                                <h6><a wire:navigate href="{{route('show.news', $item->slug)}}" class="title-hover">{{ Str::limit($item->translation->title, 37, '...')}}</a></h6>
                                                <div class="echo-de-category-read">
                                                    <a wire:navigate href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06 minute
                                                        read</a>
                                                </div>
                                            </div>
                                        </div>                                        
                                    @endforeach
                                    <div class="echo-de-category-show-more-btn">
                                        <a wire:navigate href="{{route('show.category', $category->slug)}}" class="text-capitalize echo-py-btn">@lang('messages.show_more')</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section> --}}

    <!-- Start Video Area-->
    <section class="echo-video-area">
        <div class="echo-video-content">
            <div class="container">
                <div class="echo-video-area-title-row text-center">
                    <h6>@lang('messages.videogallery')</h6>
                </div>
                <div class="echo-full-video-content">
                    <div class="row gx-6">
                        <div class="col-xl-8 col-lg-8 col-md-12">
                            <div class="echo-video-left-site">
                                <a href="https://www.youtube.com/watch?v=Ukn0u0WBpyo" class="play-video popup-youtube"><img src="{{Vite::asset('resources/images/video/image1.jpg')}}" alt="Echo"></a>
                                <div class="vedio-icone">
                                    <a class="play-video popup-youtube video-play-button" href="https://www.youtube.com/watch?v=Ukn0u0WBpyo">
                                        <span></span>
                                    </a>
                                    <div class="video-overlay">
                                        <a class="video-overlay-close">×</a>
                                    </div>
                                </div>
                                <div class="echo-video-left-site-text-box">
                                    <h5><a href="https://www.youtube.com/watch?v=Ukn0u0WBpyo" class="play-video popup-youtube title-hover">Ўзбекистоннинг кейинги 5 йилдаги мақсадлари</a></h5>
                                    <hr>
                                    <div class="echo-video-left-site-read-views">
                                        <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-12">
                            <div class="echo-video-area-home-1-right-content-responsive">
                                <div class="echo-video-right-site-content">
                                    <div class="echo-video-right-site-content-text">
                                        <h5 class="text-capitalize"><a href="https://www.youtube.com/watch?v=ucgRqEvtgH4" class="play-video popup-youtube title-hover text-white">Ўзбекистон-Озарбайжон ҳамкорлиги ...</a>
                                        </h5>
                                        <hr>
                                    </div>
                                    <div class="echo-video-right-site-content-video">
                                        <a href="https://www.youtube.com/watch?v=ucgRqEvtgH4" class="play-video popup-youtube"><img src="{{Vite::asset('resources/images/video/image2.jpg')}}" alt="Echo"></a>
                                        <div class="vedio-icone">
                                            <a class="play-video popup-youtube video-play-button" href="https://www.youtube.com/watch?v=ucgRqEvtgH4">
                                                <span></span>
                                            </a>
                                            <div class="video-overlay">
                                                <a class="video-overlay-close">×</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="echo-video-right-site-content">
                                    <div class="echo-video-right-site-content-text">
                                        <h5 class="text-capitalize"><a href="https://www.youtube.com/watch?v=6IAnGeZXXDA" class="play-video popup-youtube title-hover text-white">Ишбилармонлик фаоллиги индекси ...</a>
                                        </h5>
                                        <hr>
                                    </div>
                                    <div class="echo-video-right-site-content-video">
                                        <a href="https://www.youtube.com/watch?v=6IAnGeZXXDA" class="play-video popup-youtube"><img src="{{Vite::asset('resources/images/video/image3.jpg')}}" alt="Echo"></a>
                                        <div class="vedio-icone">
                                            <a class="play-video popup-youtube video-play-button" href="https://www.youtube.com/watch?v=6IAnGeZXXDA">
                                                <span></span>
                                            </a>
                                            <div class="video-overlay">
                                                <a class="video-overlay-close">×</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="echo-video-right-site-content">
                                    <div class="echo-video-right-site-content-text">
                                        <h5 class="text-capitalize"><a href="https://www.youtube.com/watch?v=ty76zXe7pz4" class="play-video popup-youtube title-hover text-white">Interview of the Director of the CERR ...</a></h5>
                                        <hr>
                                    </div>
                                    <div class="echo-video-right-site-content-video">
                                        <a href="https://www.youtube.com/watch?v=ty76zXe7pz4" class="play-video popup-youtube"><img src="{{Vite::asset('resources/images/video/image4.jpg')}}" alt="Echo"></a>
                                        <div class="vedio-icone">
                                            <a class="play-video popup-youtube video-play-button" href="https://www.youtube.com/watch?v=ty76zXe7pz4">
                                                <span></span>
                                            </a>
                                            <div class="video-overlay">
                                                <a class="video-overlay-close">×</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                <h4 class="text-center text-capitalize">Журналы</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row gx-5">
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/1-03-2025" target="_blank"><img src="https://static.review.uz/crop/8/0/200_265_95_807101001.jpg?v=1741943966" alt="Echo" class="img-hover"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/2-38-2025" target="_blank"><img src="https://static.review.uz/crop/2/4/200_265_95_2400471251.jpg?v=1741754941" alt="Echo" class="img-hover"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item echo-popular-news-responsive-home-1">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/2-301-2025" target="_blank"><img src="https://static.review.uz/crop/3/3/200_265_95_3357547324.jpg?v=1741754866" alt="Echo" class="img-hover"></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="echo-popular-area-single-item echo-popular-news-responsive-home-1">
                                <div class="echo-popular-area-img img-transition-scale">
                                    <a href="https://review.uz/journals/view/1-37-2025" target="_blank"><img src="https://static.review.uz/crop/9/3/200_265_95_93454726.jpg?v=1739517864" alt="Echo" class="img-hover"></a>
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
                    <img src="https://www.cesd.az/y/newlogo.jpg" class="img-fluid mb-2" style="max-height: 150px;" alt="UNDP">
                  </div>
                  <!-- Partner 4 -->
                  <div class="text-center" style="width: 250px;">
                    <img src="https://www.iddri.org/themes/custom/iddri/images/logo.svg" class="img-fluid mb-2" style="max-height: 150px;" alt="Glasgow">
                  </div>
                </div>
              </div>
      
              <!-- Slide 2 -->
              <div class="carousel-item">
                <div class="d-flex justify-content-center gap-4 flex-wrap">
                  <!-- Partner 5 -->
                  <div class="text-center" style="width: 150px;">
                    <img src="/images/partners/partner5.png" class="img-fluid mb-2" style="max-height: 50px;" alt="Partner 5">
                    <h6 class="fw-bold small">Hamkor 5</h6>
                  </div>
                  <!-- Partner 6 -->
                  <div class="text-center" style="width: 150px;">
                    <img src="/images/partners/partner6.png" class="img-fluid mb-2" style="max-height: 50px;" alt="Partner 6">
                    <h6 class="fw-bold small">Hamkor 6</h6>
                  </div>
                  <!-- Partner 7 -->
                  <div class="text-center" style="width: 150px;">
                    <img src="/images/partners/partner7.png" class="img-fluid mb-2" style="max-height: 50px;" alt="Partner 7">
                    <h6 class="fw-bold small">Hamkor 7</h6>
                  </div>
                  <!-- Partner 8 -->
                  <div class="text-center" style="width: 150px;">
                    <img src="/images/partners/partner8.png" class="img-fluid mb-2" style="max-height: 50px;" alt="Partner 8">
                    <h6 class="fw-bold small">Hamkor 8</h6>
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

@push('scripts')
<script>
    var swiper = new Swiper(".mySwiper", {
        slidesPerView: 4,
        spaceBetween: 30,
        grabCursor: true,
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        breakpoints:{
            1168:{
            slidesPerView: 4,
            },
            992:{
            slidesPerView: 3,
            },
            768:{
            slidesPerView: 2,
            },
            576:{
            slidesPerView: 1,
            },
            0:{
            slidesPerView: 1,
            },
        },
    });
</script>
@endpush

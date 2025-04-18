<div>
    <section class="echo-hero-section" data-aos="fade-up" data-aos-duration="1000">
        <div class="echo-hero">
            <div class="container">
                <div class="echo-full-hero-content">
                    <div class="row gx-5">
                        <div class="col-xl-8 col-lg-7 col-md-12">
                            <div class="echo-hero-baner">
                                <div class="echo-hero-banner-main-img  img-transition-scale">
                                    <a wire:navigate href="{{route('show.news', $main->slug)}}"><img class="banner-image-one img-hover" src="{{Vite::asset('resources/images/news/'.$main->translation->image_url)}}" alt="Echo"></a>
                                </div>
                                <h3 class="echo-hero-title text-capitalize font-weight-bold mt-3"><a href="{{route('show.news', $main->slug)}}" class="title-hover">{{$main->translation->title}}</h3>
                                <hr>
                                <p class="echo-hero-discription">{{$main->translation->short_description}}</p>
                                <div class="echo-hero-area-titlepost-post-like-comment-share">
                                    <div class="echo-hero-area-like-read-comment-share">
                                        <a wire:navigate href="{{route('show.news', $main->slug)}}"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                    </div>
                                    <div class="echo-hero-area-like-read-comment-share">
                                        <a wire:navigate href="{{route('show.news', $main->slug)}}"><i class="fa-light fa-eye"></i> {{$main->view_count}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-5 col-md-12">
                            <div class="echo-home-1-hero-area-top-story">
                                <h6>@lang('messages.popular')</h6>
                                @foreach ($popular_news as $key => $popular)
                                    @php
                                        $translation = $popular->translation;
                                    @endphp
                                    @if ($key == 0)
                                        <div class="echo-top-story first">
                                            <div class="echo-story-picture img-transition-scale">
                                                <a wire:navigate href="{{route('show.news', $popular->slug)}}"><img src="{{Vite::asset('resources/images/news/'.$translation->image_url)}}" alt="Echo" class="img-hover"></a>
                                            </div>
                                            <div class="echo-story-text">
                                                <h4><a wire:navigate href="{{route('show.news', $popular->slug)}}" class="title-hover">{{$translation->title}}</a></h4>
                                                <div class="echo-trending-post-bottom-icons">
                                                    <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                                    <a href="#" class="pe-none"><i class="fa-light fa-eye"></i> {{$popular->view_count}}</a>
                                                </div>
                                            </div>
                                        </div>                                
                                    @else
                                        <div class="echo-top-story">
                                            <div class="echo-story-picture img-transition-scale">
                                                <a wire:navigate href="{{route('show.news', $popular->slug)}}"><img src="{{Vite::asset('resources/images/news/'.$translation->image_url)}}" alt="Echo" class="img-hover"></a>
                                            </div>
                                            <div class="echo-story-text">
                                                <h4><a wire:navigate href="{{route('show.news', $popular->slug)}}" class="title-hover">{{$translation->title}}</a></h4>
                                                <a wire:navigate href="{{route('show.news', $popular->slug)}}" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="echo-latest-news-area" data-aos="fade-left" data-aos-duration="600" data-aos-delay="100">
        <div class="echo-latest-news-content">
            <div class="container">
                <div class="echo-be-slider-btn">
                    <div class="echo-latest-nw-title">
                        <h4>@lang('messages.latest_news')</h4>
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
                                        <div class="echo-latest-news-single-title">
                                            <h5><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{ Str::limit($news->translation->title, 40, '...')}}</a></h5>
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
    </section>

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

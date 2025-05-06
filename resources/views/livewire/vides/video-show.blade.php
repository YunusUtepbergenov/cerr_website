<div>
    <section class="echo-hero-section inner inner-post">
        <div class="echo-hero">
            <div class="container">
                <div class="echo-full-hero-content">
                    <div class="row gx-5 sticky-coloum-wrap">
                        <div class="col-xl-8 col-lg-8">
                            <div class="echo-hero-baner">
                                <div class="echo-inner-img-ct-1  img-transition-scale">
                                    <a class="play-video popup-youtube" href="{{$video->url}}"><img src="{{ asset('images/video/' . $video->image) }}" alt="Echo" class="post-style-1-frist-hero-img"></a>
                                </div>
                                <h2 class="echo-hero-title text-capitalize font-weight-bold"><a href="post-details.html" class="title-hover">{{$video->title}}</a></h2>
                                <div class="echo-hero-area-titlepost-post-like-comment-share">
                                    <div class="echo-hero-area-like-read-comment-share">
                                        <a href="#"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                    </div>
                                    <div class="echo-hero-area-like-read-comment-share">
                                        <a href="#"><i class="fa-light fa-eye"></i> 3.5k Views</a>
                                    </div>
                                    <div class="echo-hero-area-like-read-comment-share">
                                        <a href="#"><i class="fa-light fa-comment-dots"></i> 05 Comment</a>
                                    </div>
                                    <div class="echo-hero-area-like-read-comment-share">
                                        <a href="#"><i class="fa-light fa-arrow-up-from-bracket"></i> 1.5k Share</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 sticky-coloum-item">
                            <div class="echo-right-ct-1">
                                <div class="echo-home-1-hero-area-top-story">
                                    <h5 class="text-center">@lang('messages.popular')</h5>
                                    @foreach($popular_news as $news)
                                        <div class="echo-top-story">
                                            <div class="echo-story-picture img-transition-scale">
                                                <a href="{{route('show.news', $news->slug)}}"><img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover"></a>
                                            </div>
                                            <div class="echo-story-text">
                                                <h6><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                                <a href="{{route('show.news', $news->slug)}}" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

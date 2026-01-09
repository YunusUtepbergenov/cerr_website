<div>
    <section class="echo-hero-section inner inner-post">
        <div class="echo-hero">
            <div class="container">
                <div class="echo-full-hero-content">
                    <div class="row gx-5 sticky-coloum-wrap">
                        <div class="col-xl-8 col-lg-8">
                            <div class="echo-hero-baner">
                                <h2 class="echo-hero-title font-weight-bold">{{$news->translation->title}}</h2>
                                <div class="entry-content post-info">
                                    <p class="news-short-desc">{{$news->translation->short_description}}</p>
                                </div>
                                <div class="echo-inner-img-ct-1 img-transition-scale mb-5">
                                    <img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="{{$news->translation->title}}" class="post-style-1-frist-hero-img">
                                </div>
                                {{-- <div class="echo-hero-area-titlepost-post-like-comment-share">
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
                                </div> --}}
                                {{-- <p class="echo-hero-discription"> --}}
                                    {!!$news->translation->content!!}
                                {{-- </p> --}}
                            </div>
{{-- 
                            <div class="echo-more-news-area">
                                <h3 class="title">Похожие новости</h3>
                                <div class="inner">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="echo-top-story">
                                                <div class="echo-story-picture img-transition-scale">
                                                    <a href="post-details.html"><img src="https://static.review.uz/crop/1/0/825__95_1026418413.jpg?v=1740389360" alt="Echo" class="img-hover"></a>
                                                </div>
                                                <div class="echo-story-text">
                                                    <h6><a href="#" class="title-hover">Современные институты — ...</a></h6>
                                                    <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="echo-top-story">
                                                <div class="echo-story-picture img-transition-scale">
                                                    <a href="post-details.html"><img src="https://static.review.uz/crop/3/9/825__95_3967625511.jpg?v=1740388841" alt="Echo" class="img-hover"></a>
                                                </div>
                                                <div class="echo-story-text">
                                                    <h6><a href="#" class="title-hover">Экономические тренды...</a></h6>
                                                    <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>

                        <div class="col-xl-4 col-lg-4 sticky-coloum-item">
                            <div class="echo-right-ct-1">
                                <div class="echo-home-1-hero-area-top-story">
                                    <h5 class="text-center">@lang('messages.popular')</h5>
                                    @foreach($popular_news as $news)
                                        <div class="echo-top-story" wire:key="popular-news-{{ $news->id }}">
                                            <div class="echo-story-picture img-transition-scale">
                                                <a href="{{route('show.news', $news->slug)}}"><img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover"></a>
                                            </div>
                                            <div class="echo-story-text">
                                                <h6><a href="{{route('show.news', $news->slug)}}" class="title-hover">{{$news->translation->title}}</a></h6>
                                                <a href="{{route('show.news', $news->slug)}}" class="pe-none"><i class="fa-light fa-clock"></i> {{ \Carbon\Carbon::parse($news->created_at)->format('d-m-Y') }}</a>
                                            </div>
                                        </div>
                                        <hr>
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

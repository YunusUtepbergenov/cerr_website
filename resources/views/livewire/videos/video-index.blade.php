<div>
    <div class="echo-breadcrumb-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title">@lang('messages.videogallery')</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="echo-hero-section inner inner-2">
        <div class="echo-hero">
            <div class="container">
                <div class="echo-full-hero-content">
                    <div class="row gx-5 sticky-coloum-wrap">
                        <div class="col-xl-9 col-lg-9">
                            <div class="row" wire:ignore.self>
                                @foreach ($videos as $video)
                                        <div class="col-lg-6" wire:key="video-{{ $video->id }}">
                                            <div class="echo-hero-baner my-5">
                                                <div class="echo-inner-img-ct-1 img-transition-scale">
                                                    <a href="{{ $video->url }}"><img src="{{asset('images/video/' . $video->image)}}" alt="Echo"></a>
                                                    <div class="echo-hm2-video-icons">
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
                                                <div class="echo-banner-texting my-3">
                                                    <h5 class="echo-hero-title font-weight-bold text-center">
                                                        <a href="{{ $video->url }}" class="title-hover play-video popup-youtube">{{$video->title}}</a>
                                                    </h5>
                                                </div>
                                            </div>
                                        </div>
                                @endforeach
                            </div>
                            @if($perPage < $totalCount)
                                <div class="echo-de-category-show-more-btn text-center">
                                    <a href="#" wire:click.prevent="loadMore" class="echo-py-btn">@lang('messages.load_more')</a>
                                </div>
                            @endif
                            </div>
                        <div class="col-xl-3 col-lg-3 sticky-coloum-item">
                            <div class="echo-home-1-hero-area-top-story bg-right-side">
                                <h5 class="text-center">@lang('messages.popular')</h5>

                                <hr style="background-color: #4c0505; margin-top: 10px;"> 
                                
                                @foreach ($popular_news as $news)
                                    <div class="echo-top-story first" wire:key="popular-news-{{ $news->id }}">
                                        <div class="echo-story-picture img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}"><img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover"></a>
                                        </div>

                                        <div class="echo-story-text">
                                            <h4><a href="#" class="title-hover">{{$news->translation->title}}</a></h4>
                                            <div class="echo-trending-post-bottom-icons">
                                                <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> {{ \Carbon\Carbon::parse($news->created_at)->format('d.m.Y') }}</a>
                                                <a href="#" class="pe-none"><i class="fa-light fa-eye"></i> {{$news->view_count}}</a>
                                            </div>
                                        </div>
                                    </div>
                                    <hr style="background-color: #4c0505;">                                    
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
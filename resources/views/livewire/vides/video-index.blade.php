<div>
    <div class="echo-breadcrumb-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="breadcrumb-inner text-center">
                        <h1 class="title">Videos</h1>
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
                            @foreach ($videos as $video)
                                <div class="echo-hero-baner">
                                    <div class="echo-inner-img-ct-1 img-transition-scale">
                                        <a href="{{ route('videos.show', $video->id) }}">
                                            <img src="{{ asset('images/video/' . $video->image) }}" alt="{{ $video->title }}">
                                        </a>
                                    </div>
                                    <div class="echo-banner-texting">
                                        <h3 class="echo-hero-title text-capitalize font-weight-bold">
                                            <a href="{{ route('videos.show', $video->id) }}" class="title-hover">{{ $video->title }}</a>
                                        </h3>
                                        <div class="echo-hero-area-titlepost-post-like-comment-share">
                                            <div class="echo-hero-area-like-read-comment-share">
                                                <a href="{{ route('videos.show', $video->id) }}">
                                                    <i class="fa-light fa-clock"></i> {{ date('d-m-Y', strtotime($video->created_at)) }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>                                
                            @endforeach
                            
                            <div class="d-flex justify-content-center mt-4">
                                {{ $videos->links() }}
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-3 sticky-coloum-item">
                            <div class="echo-home-1-hero-area-top-story bg-right-side">
                                <h5 class="text-center">@lang('messages.popular')</h5>
                                <hr style="background-color: #4c0505; margin-top: 10px;"> 
                                @foreach ($popular_news as $news)
                                    <div class="echo-top-story first">
                                        <div class="echo-story-picture img-transition-scale">
                                            <a href="{{route('show.news', $news->slug)}}"><img src="{{asset('images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover"></a>
                                        </div>
                                        <div class="echo-story-text">
                                            <h4><a href="#" class="title-hover">{{$news->translation->title}}</a></h4>
                                            <div class="echo-trending-post-bottom-icons">
                                                <a href="#" class="pe-none"><i class="fa-light fa-clock"></i> 06.03.2025</a>
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
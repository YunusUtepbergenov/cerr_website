<div>
    <section class="echo-hero-section inner inner-post">
        <div class="echo-hero">
            <div class="container">
                <div class="echo-full-hero-content">
                    <div class="row gx-5 sticky-coloum-wrap">
                        <div class="col-xl-8 col-lg-8">
                            <div class="echo-hero-baner">
                                <div class="echo-inner-img-ct-1  img-transition-scale">
                                    <a href="#"><img src="{{$page->translation->image}}" alt="Echo" class="post-style-1-frist-hero-img"></a>
                                </div>
                                <h3 class="echo-hero-title text-capitalize font-weight-bold"><a href="#" class="title-hover">{{$page->translation->title}}</a></h3>

                                <p>
                                    {!!$page->translation->content!!}
                                </p>                            
                            </div>
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
                                                <a href="{{route('show.news', $news->slug)}}" class="pe-none"><i class="fa-light fa-clock"></i> {{ \Carbon\Carbon::parse($news->created_at)->format('d.m.Y') }}</a>
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

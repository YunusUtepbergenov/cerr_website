<div>
    <section class="echo-banner-innerpage">
        <div class="container">
            <div class="banner-inner">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-image top">
                            <img src="{{asset('images/cerr.jpg')}}" alt="Echo">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="echo-hero-section inner inner-post inner-post-3">
        <div class="echo-hero">
            <div class="container">
                <div class="echo-full-hero-content">
                    <div class="row gx-5 sticky-coloum-wrap">
                        <div class="col-xl-12">
                            <div class="echo-hero-baner" style="text-align: justify;">
                                <h2 class="echo-hero-title text-capitalize font-weight-bold" style="text-align: center;"><a href="post-details.html" class="title-hover">{{$page->translation->title}}</a></h2>
                                <div class="echo-hero-area-titlepost-post-like-comment-share">
                                </div>
                                <hr>
                                {!!$page->translation->content!!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

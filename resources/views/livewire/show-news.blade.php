<div>
    <section class="echo-hero-section inner inner-post">
        <div class="echo-hero">
            <div class="container">
                <div class="echo-full-hero-content">
                    <div class="row gx-5 sticky-coloum-wrap">
                        <div class="col-xl-8 col-lg-8">
                            <div class="echo-hero-baner">
                                {{-- @dd($news) --}}
                                <div class="echo-inner-img-ct-1  img-transition-scale">
                                    <a href="post-details.html"><img src="{{Vite::asset('resources/images/news/'.$news->translation->image_url)}}" alt="Echo" class="post-style-1-frist-hero-img"></a>
                                </div>
                                <h2 class="echo-hero-title text-capitalize font-weight-bold"><a href="post-details.html" class="title-hover">{{$news->translation->title}}</a></h2>
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
                                <p class="echo-hero-discription">
                                    {{$news->translation->content}}
                                </p>
                                <p class="echo-hero-discription">По результатам опроса Центра экономических исследований и реформ (ЦЭИР), в декабре 2022 г. сводный индикатор бизнес-климата вырос на 4 пункта и составил 
                                    57 пунктов, что оценивает состояние бизнес-климата в стране как положительное.
                                </p>
                                <p class="echo-hero-discription"><i>Справочно: ЦЭИР ежемесячно проводит опрос среди 1 тыс. предпринимателей по всей стране, представляющих различные сектора экономики.</i></p>
                                <p class="echo-hero-discription"><i>Сводный индекс бизнес-климата формируется на основе значений индикаторов текущего состояния и ожидания. Те, в свою очередь, демонстрируют 
                                    уровень тревожности в отношении имеющихся препятствий в введении бизнеса.</i>
                                </p>
                                <p>В декабре на рост&nbsp;<em>сводного индекса</em>&nbsp;значительное влияние оказала динамика индикаторов в секторе строительства и промышленности. Улучшения также наблюдались в сельском хозяйстве и в сфере услуг.</p>
                                <p><strong>43%</strong>&nbsp;респондентов оценили текущее состояние своего бизнеса как «<strong>хорошее</strong>», доля отметивших как «<strong>плохое</strong>» составила 11%.</p>
                                <p><strong>20%</strong>&nbsp;предприятий увеличили количество работников, а&nbsp;<strong>40%</strong>&nbsp;предпринимателей отметили рост спроса на товары/услуги.</p>
                            </div>
                            <div class="echo-financial-area">
                                <div class="image-area">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12">
                                            <img src="https://static.review.uz/uploads/1/hjwqC0S7efFY4Pw8SnPJtDsB49q4E2XH.png" alt="blog" class="post-style-1-new-img">
                                        </div>
                                    </div>
                                </div>
                                <div class="content">
                                    <p>Индикатор ожидания&nbsp;<u>перспектив развития бизнеса</u>&nbsp;в ближайшие 3 месяца остается на достаточно высоком уровне – 67 пунктов, что поддерживается высокой оптимистичностью в сфере сельского хозяйства – 80 пунктов и строительства – 81 пунктов.</p>
                                    <p>Доля предпринимателей, ожидающих улучшение общего состояния бизнеса в ближайшие 3 месяца составила – 75%, 64% – ожидают дальнейшее повышение спроса на товары и услуги, еще 64% – планируют увеличить количество работников. 24% предпринимателей ожидают повышение цен на их товары/услуги в ближайшие 3 месяца.</p>
                                    {{-- <div class="row  align-items-center">
                                        <div class="col-lg-6 col-md-6 col-sm-12">
                                            <div class="details-tag">
                                                <h6>Tags:</h6>
                                                <button>Finance</button>
                                                <button>Economic</button>
                                                <button>Bank</button>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>
                            </div>

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
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 sticky-coloum-item">
                            <div class="echo-right-ct-1">
                                <div class="echo-home-1-hero-area-top-story">
                                    <h5 class="text-center">@lang('messages.popular')</h5>
                                    @foreach($popular_news as $news)
                                        <div class="echo-top-story">
                                            <div class="echo-story-picture img-transition-scale">
                                                <a href="{{route('show.news', $news->slug)}}"><img src="{{Vite::asset('resources/images/news/'.$news->translation->image_url)}}" alt="Echo" class="img-hover"></a>
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

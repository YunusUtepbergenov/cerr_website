<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Center for Economic Research and Reforms</title>
    <link rel="shortcut icon" href="https://cer.uz/themes/cer/icon/favicon.ico" type="image/x-icon">

    @vite([
                'resources/css/plugins/fontawesome-5.css',
                'resources/css/app.css',
                'resources/css/vendor/swiper.css',
                'resources/css/vendor/metismenu.css', 
                'resources/css/vendor/magnific-popup.css',
                'resources/css/style.css',
        ])
    
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">

</head>

<body class="home-one">
    <!-- Start Top Header Area  #495981 -->
    <header class="echo-header-area">
        <div class="echo-header-top">
            <div class="container">
                <div class="echo-full-header-top">
                    <div class="row align-items-center plr_md--30">
                        <div class="col-xl-4 col-lg-4 d-none d-lg-block">

                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-7 col-8">
                            <div class="language-dropdown">
                                <a href="/oz" class="">Ўзбекча</a>
                                <a href="/uz" class="">O'zbekcha</a>
                                <a href="/ru" class="active">Русский</a>
                                <a href="/en" class="">English</a>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-5 col-4 position-relative">
                            <div class="echo-header-top-subs-social-menu">
                                <div class="echo-header-top-subs-social">
                                    <div class="echo-home-1-social-media-icons">
                                        <ul class="list-unstyled social-area list-group list-group-horizontal">
                                            <li class="list-group-item"><a href="https://www.facebook.com/CERR.Uzbekistan" target="_blank"><i class="fa-brands fa-facebook-f"></i></a></li>
                                            <li class="list-group-item"><a href="https://t.me/cerruz" target="_blank"><i class="fa-brands fa-telegram"></i></a></li>
                                            <li class="list-group-item"><a href="https://www.instagram.com/cerr.uz/" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
                                            <li class="list-group-item"><a href="https://www.youtube.com/@centerforeconomicresearcha1331/featured" target="_blank"><i class="fa-brands fa-youtube"></i></a></li>
                                        </ul>
                                    </div>
                                    <div class="echo-header-top-social-media">
                                        <div class="rts-darkmode">
                                            <a id="rts-data-toggle" class="rts-dark-light">
                                                <i class="rts-go-dark fal fa-moon"></i>
                                                <i class="rts-go-light far fa-sun"></i>
                                            </a>
                                        </div>
                                        <a href="#" id="search" class="echo-header-top-search-btn search-icon action-item icon">
                                            <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.11544 16.961C13.3484 16.961 16.7798 13.5296 16.7798 9.29665C16.7798 5.06373 13.3484 1.63226 9.11544 1.63226C4.88251 1.63226 1.45105 5.06373 1.45105 9.29665C1.45105 13.5296 4.88251 16.961 9.11544 16.961Z" stroke="#5E5E5E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="M14.4461 15.0254L17.451 18.0225" stroke="#5E5E5E" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                        <div class="search-input-area">
                                            <div class="container">
                                                <div class="search-input-inner">
                                                    <div class="input-div">
                                                        <input id="searchInput1" class="search-input" type="text" placeholder="Search by keyword or #">
                                                    </div>
                                                    <div class="search-close-icon"><i class="fa-regular fa-xmark-large rt-xmark"></i></div>
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
        </div>
        <!-- Start Home-1 Menu & Site Logo & Social Media -->
        <div class="echo-home-1-menu">
            <div class="echo-site-main-logo-menu-social">
                <div class="container">
                    <div class="row align-items-center plr_md--30 plr_sm--30 plr--10">
                        <div class="col-xl-2 col-lg-2 col-md-7 col-sm-7 col-7">
                            <div class="echo-site-logo">
                                <a class="logo-light" href="index.html"><img src="https://cer.uz/themes/cer/img/logo.svg" alt="Echo"></a>
                                <a class="logo-dark" href="index.html"><img src="https://cer.uz/themes/cer/img/logo.svg" alt="Echo"></a>
                            </div>
                        </div>
                        <div class="col-xl-10 col-lg-10 d-none d-lg-block">
                            <nav>
                                <div class="echo-home-1-menu">
                                    <ul class="list-unstyled echo-desktop-menu">
                                        <li class="menu-item">
                                            <a href="{{route('home')}}" class="echo-dropdown-main-element active">Главная</a>
                                        </li>
                                        <li class="menu-item echo-has-dropdown">
                                            <a href="#" class="echo-dropdown-main-element">О центре</a>
                                            <ul class="echo-submenu list-unstyled menu-pages">
                                                <li class="nav-item"><a href="about.html">О нас</a></li>
                                                <li class="nav-item"><a href="leadership.html">Руководство </a></li>
                                                <li class="nav-item"><a href="404.html">Структура центра </a></li>
                                                {{-- <li class="nav-item"><a href="404.html">Филиалы центра</a></li> --}}
                                                <li class="nav-item"><a href="404.html">Сектора центра</a></li>
                                                {{-- <li class="nav-item"><a href="404.html">Международное сотрудничество</a></li>
                                                <li class="nav-item"><a href="404.html">Нормативно-правовая база </a></li>
                                                <li class="nav-item"><a href="404.html">Противодействие коррупции  </a></li>
                                                <li class="nav-item"><a href="404.html">Государственные закупки </a></li> --}}
                                            </ul>
                                        </li>
                                        {{-- <li class="menu-item echo-has-dropdown">
                                            <a href="#" class="echo-dropdown-main-element">Исследования Центра</a>
                                            <ul class="echo-submenu list-unstyled menu-pages">
                                                <li class="nav-item"><a href="404.html">Архив</a></li>
                                            </ul>
                                        </li> --}}
                                        {{-- <li class="menu-item echo-has-dropdown">
                                            <a href="#" class="echo-dropdown-main-element">События</a>
                                            <ul class="echo-submenu list-unstyled menu-pages">
                                                <li class="nav-item"><a href="404.html">Конференции</a></li>
                                                <li class="nav-item"><a href="404.html"></a>Информация о предстоящих конференциях</li>
                                            </ul>
                                        </li> --}}
                                        <li class="menu-item echo-has-dropdown">
                                            <a href="#" class="echo-dropdown-main-element">Пресс-служба</a>
                                            <ul class="echo-submenu list-unstyled menu-pages">
                                                <li class="nav-item"><a href="404.html">Пресс-релизы </a></li>
                                                <li class="nav-item"><a href="404.html">Фотогалерея</a></li>
                                                <li class="nav-item"><a href="404.html">Видеогалерея</a></li>
                                            </ul>
                                        </li>
                                        <li class="menu-item"><a href="contact.html" class="echo-dropdown-main-element">Вакансии</a></li>
                                        <li class="menu-item"><a href="contact.html" class="echo-dropdown-main-element">Контакты</a></li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Home-1 Menu & Site Logo & Social Media -->
    </header>
    <!-- End Top Header Area -->
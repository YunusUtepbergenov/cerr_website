<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Ardet предлагает высококачественные строительные материалы, включая краски, клеи и промышленные решения, разработанные специально для рынка Узбекистана.">
    <meta name="keywords" content="Ardet, строительные материалы Узбекистан, клеи, краски, строительные материалы, промышленные решения">
    <link rel="canonical" href="https://ardet.uz" />

    <title>Center for Economic Research and Reforms</title>
    <link rel="shortcut icon" href="https://cer.uz/themes/cer/icon/favicon.ico" type="image/x-icon">
    
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css">
    @stack('styles')

</head>

<body class="home-one">
    <div class="alert alert-warning text-center mb-0" role="alert">
        @lang('messages.test_mode')
    </div>

    <header class="echo-header-area">
        <div class="echo-header-top">
            <div class="container">
                <div class="echo-full-header-top">
                    <div class="row align-items-center plr_md--30">
                        <div class="col-xl-4 col-lg-4 d-none d-lg-block">
                        </div>

                        <div class="col-xl-4 col-lg-4 d-none d-lg-block">
                            <div class="language-dropdown">
                                <a href="{{route('lang.switch', 'kr')}}" class="{{(Config::get('app.locale') == 'kr') ? 'active' : ''}}">Ўзбекча</a>
                                <a href="{{route('lang.switch', 'uz')}}" class="{{(Config::get('app.locale') == 'uz') ? 'active' : ''}}">O'zbekcha</a>
                                <a href="{{route('lang.switch', 'ru')}}" class="{{(Config::get('app.locale') == 'ru') ? 'active' : ''}}">Русский</a>
                                <a href="{{route('lang.switch', 'en')}}" class="{{(Config::get('app.locale') == 'en') ? 'active' : ''}}">English</a>
                            </div>
                        </div>

                         <!-- Language Dropdown - Mobile Version -->
                        <div class="col-6 d-lg-none" x-data="{ langDropdownOpen: false }" 
                            @click.away="langDropdownOpen = false" 
                            @keydown.escape.window="langDropdownOpen = false">
                            <div class="mobile-language-dropdown">
                                <button @click="langDropdownOpen = !langDropdownOpen" class="lang-toggle-btn">
                                    @php
                                        $currentLang = Config::get('app.locale');
                                        $langNames = [
                                            'kr' => 'Ўзбекча',
                                            'uz' => 'O\'zbekcha',
                                            'ru' => 'Русский',
                                            'en' => 'English'
                                        ];
                                    @endphp
                                    {{ $langNames[$currentLang] ?? 'Language' }} <i class="fas fa-chevron-down ms-1"></i>
                                </button>
                                <div x-show="langDropdownOpen" x-transition class="mobile-lang-options">
                                    <a href="{{route('lang.switch', 'kr')}}" class="{{(Config::get('app.locale') == 'kr') ? 'active' : ''}}">Ўзбекча</a>
                                    <a href="{{route('lang.switch', 'uz')}}" class="{{(Config::get('app.locale') == 'uz') ? 'active' : ''}}">O'zbekcha</a>
                                    <a href="{{route('lang.switch', 'ru')}}" class="{{(Config::get('app.locale') == 'ru') ? 'active' : ''}}">Русский</a>
                                    <a href="{{route('lang.switch', 'en')}}" class="{{(Config::get('app.locale') == 'en') ? 'active' : ''}}">English</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-5 col-4 position-relative ml-3">
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
        <div x-data="{ mobileMenuOpen: false, aboutDropdownOpen: false, pressDropdownOpen: false }" 
        x-init="window.addEventListener('resize', () => { 
            if (window.innerWidth >= 992) { 
                mobileMenuOpen = false;
                aboutDropdownOpen = false;
                pressDropdownOpen = false;
            } 
        })">
            <div class="echo-home-1-menu">
                <div class="echo-site-main-logo-menu-social">
                    <div class="container">
                        <div class="row align-items-center plr_md--30 plr_sm--30 plr--10">
                            
                            <div class="col-xl-2 col-lg-2 col-md-7 col-sm-7 col-7">
                                <div class="echo-site-logo">
                                    <a class="logo-light" wire:navigate href="{{route('home')}}"><img src="{{asset('images/logo.svg')}}" alt="Echo"></a>
                                    <a class="logo-dark" wire:navigate href="{{route('home')}}"><img src="{{asset('images/logo.svg')}}" alt="Echo"></a>
                                </div>
                            </div>

                            <!-- Hamburger Menu Button (visible only on mobile) -->
                            <div class="col-md-5 col-sm-5 col-5 d-lg-none">
                                <div class="echo-mobile-menu-toggle text-end">
                                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="navbar-toggler" type="button" aria-label="Toggle navigation">
                                        <i class="fas fa-bars"></i>
                                    </button>                            
                                </div>
                            </div>

                            <div class="col-xl-10 col-lg-10 d-none d-lg-block">
                                <nav>
                                    <div class="echo-home-1-menu">
                                        <ul class="list-unstyled echo-desktop-menu">
                                            <li class="menu-item">
                                                <a wire:navigate href="{{route('home')}}" class="echo-dropdown-main-element active">@lang('messages.main')</a>
                                            </li>
                                            <li class="menu-item echo-has-dropdown">
                                                <a href="#" class="echo-dropdown-main-element">@lang('messages.about')</a>
                                                <ul class="echo-submenu list-unstyled menu-pages">
                                                    <li class="nav-item"><a wire:navigate href="{{route('about')}}">@lang('messages.objectives')</a></li>
                                                    <li class="nav-item"><a wire:navigate href="{{route('history')}}">@lang('messages.history')</a></li>
                                                    <li class="nav-item"><a wire:navigate href="{{route('leadership')}}">@lang('messages.leadership') </a></li>
                                                    <li class="nav-item"><a wire:navigate href="{{route('structure')}}">@lang('messages.structure') </a></li>
                                                    {{-- <li class="nav-item"><a href="404.html">Филиалы центра</a></li> --}}
                                                    {{-- <li class="nav-item"><a href="404.html">Сектора центра</a></li> --}}
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
                                                <a href="#" class="echo-dropdown-main-element">@lang('messages.press')</a>
                                                <ul class="echo-submenu list-unstyled menu-pages">
                                                    <li class="nav-item"><a href="{{route('show.category', 'research')}}">@lang('messages.press_releases') </a></li>
                                                    {{-- <li class="nav-item"><a href="404.html">@lang('messages.photogallery')</a></li> --}}
                                                    <li class="nav-item"><a href="{{route('videos.index')}}">@lang('messages.videogallery')</a></li>
                                                </ul>
                                            </li>
                                            <li class="menu-item"><a wire:navigate href="{{route('vacancies')}}" class="echo-dropdown-main-element">@lang('messages.vacancies')</a></li>
                                            <li class="menu-item"><a wire:navigate href="{{route('contact')}}" class="echo-dropdown-main-element">@lang('messages.contacts')</a></li>
                                        </ul>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu (Collapsible) -->
                <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200" 
                    x-transition:enter-start="opacity-0 transform -translate-y-2" 
                    x-transition:enter-end="opacity-100 transform translate-y-0" 
                    x-transition:leave="transition ease-in duration-200" 
                    x-transition:leave-start="opacity-100 transform translate-y-0" 
                    x-transition:leave-end="opacity-0 transform -translate-y-2" 
                    class="echo-mobile-menu-wrapper">
                    <div class="container">
                        <div class="echo-mobile-menu-content">
                            <ul class="list-unstyled">
                                <li class="menu-item">
                                    <a wire:navigate href="{{route('home')}}" class="echo-dropdown-main-element active">@lang('messages.main')</a>
                                </li>
                                <li class="menu-item mobile-dropdown">
                                    <a href="#" @click.prevent="aboutDropdownOpen = !aboutDropdownOpen" class="echo-dropdown-main-element d-flex justify-content-between align-items-center">
                                        @lang('messages.about')
                                        <i :class="aboutDropdownOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="ms-2"></i>
                                    </a>
                                    <div x-show="aboutDropdownOpen" x-transition class="mt-2">
                                        <ul class="list-unstyled ps-3">
                                            <li class="nav-item py-2"><a wire:navigate href="{{route('about')}}">@lang('messages.objectives')</a></li>
                                            <li class="nav-item py-2"><a wire:navigate href="{{route('history')}}">@lang('messages.history')</a></li>
                                            <li class="nav-item py-2"><a wire:navigate href="{{route('leadership')}}">@lang('messages.leadership') </a></li>
                                            <li class="nav-item py-2"><a wire:navigate href="{{route('structure')}}">@lang('messages.structure') </a></li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="menu-item mobile-dropdown">
                                    <a href="#" @click.prevent="pressDropdownOpen = !pressDropdownOpen" class="echo-dropdown-main-element d-flex justify-content-between align-items-center">
                                        @lang('messages.press')
                                        <i :class="pressDropdownOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="ms-2"></i>
                                    </a>
                                    <div x-show="pressDropdownOpen" x-transition class="mt-2">
                                        <ul class="list-unstyled ps-3">
                                            <li class="nav-item py-2"><a href="http://192.168.1.49:8000/show-category/maxime-error">@lang('messages.press_releases') </a></li>
                                            <li class="nav-item py-2"><a href="404.html">@lang('messages.photogallery')</a></li>
                                            <li class="nav-item py-2"><a href="404.html">@lang('messages.videogallery')</a></li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="menu-item"><a wire:navigate href="{{route('vacancies')}}" class="echo-dropdown-main-element">@lang('messages.vacancies')</a></li>
                                <li class="menu-item"><a wire:navigate href="{{route('contact')}}" class="echo-dropdown-main-element">@lang('messages.contacts')</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Home-1 Menu & Site Logo & Social Media -->
    </header>
    <!-- End Top Header Area -->
        {{ $slot }}
    <!-- Start Footer Area -->
    <footer class="echo-footer-area" id="footer">
        <div class="container">
            <div class="echo-row">
                <div class="echo-footer-content-1">
                    <div class="echo-get-in-tuch">
                        <!-- <h4 class="text-capitalize">CERR</h4> -->
                        <div class="echo-site-logo">
                            <a class="logo-light" href="index.html"><img src="https://cer.uz/themes/cer/img/logo.svg" alt="Echo"></a>
                            <a class="logo-dark" href="index.html"><img src="https://cer.uz/themes/cer/img/logo.svg" alt="Echo"></a>
                        </div>
                    </div>
                    <div class="echo-footer-address">
                        <span class="text-capitalize"><i class="fa-regular fa-map"></i> @lang('messages.address')</span>
                        <span class="text-capitalize"><i class="fa-regular fa-phone"></i> +78 150 02 02</span>
                        <span class="text-capitalize"><i class="fa-sharp fa-regular fa-envelope"></i>
                            info@cerr.uz</span>
                        <div class="echo-footer-social-media">
                            <a href="https://www.facebook.com/CERR.Uzbekistan">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>
                            <a href="https://t.me/cerruz">
                                <i class="fa-brands fa-telegram"></i>
                            </a>
                            <a href="https://www.instagram.com/cerr.uz/">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                            <a href="https://www.youtube.com/@centerforeconomicresearcha1331/featured">
                                <i class="fa-brands fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="echo-footer-content-2">
                    <div class="echo-get-in-tuch">
                        <h4 class="text-capitalize">@lang('messages.about')</h4>
                    </div>
                    <div class="echo-footer-help">
                        <ul class="list-unstyled">
                            <li><a href="{{route('about')}}">@lang('messages.objectives')</a></li>
                            <li><a href="{{route('leadership')}}">@lang('messages.leadership')</a></li>
                            <li><a href="{{route('structure')}}">@lang('messages.structure')</a></li>
                            {{-- <li><a href="#">Сектора центра</a></li>
                            <li><a href="#">Международное сотрудничество</a></li>
                            <li><a href="#">Нормативно-правовая база </a></li> --}}
                        </ul>
                    </div>
                </div>
                <div class="echo-footer-content-3">
                    <div class="echo-get-in-tuch">
                        <h4 class="text-capitalize">@lang('messages.categories')</h4>
                    </div>
                    <div class="echo-footer-help">
                        <ul class="list-unstyled">
                            {{-- <li><a href="#">Исследования </a></li> --}}
                            <li><a href="#">@lang('messages.press')</a></li>
                            {{-- <li><a href="#">События</a></li> --}}
                            <li><a href="#">@lang('messages.vacancies')</a></li>
                            <li><a href="#">@lang('messages.contacts')</a></li>
                        </ul>
                    </div>
                </div>
                <div class="echo-footer-content-4">
                    <div class="echo-get-in-tuch">
                        <h4 class="text-capitalize">@lang('messages.useful_links')</h4>
                    </div>
                    <div class="echo-footer-help">
                        <ul class="list-unstyled">
                            <li><a href="https://president.uz/uz" target="_blank">@lang('messages.president_uz')</a></li>
                            <li><a href="https://parliament.gov.uz/" target="_blank">@lang('messages.parliament')</a></li>
                            <li><a href="https://www.senat.uz" target="_blank">@lang('messages.senat')</a></li>
                            <li><a href="https://gov.uz/" target="_blank">@lang('messages.gov_uz')</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer Area -->

    <!-- Start Scricpt Area -->

    <!--scroll top button-->
    <button class="scroll-top-btn">
        <i class="fa-regular fa-angles-up"></i>
    </button>
    <!--scroll top button end-->

    <div id="anywhere-home"></div>

    @vite([
        'resources/css/plugins/fontawesome-5.css',
        'resources/js/app.js',
        'resources/css/style.css',
        ])
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        AOS.init();
        document.addEventListener('DOMContentLoaded', function() {
    // Handle opening/closing of the mobile menu
    const mobileMenuToggle = document.querySelector('.navbar-toggler');
    const mobileMenuWrapper = document.getElementById('echoMobileMenu');
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            if (mobileMenuWrapper.classList.contains('show')) {
                mobileMenuWrapper.classList.remove('show');
            } else {
                mobileMenuWrapper.classList.add('show');
            }
        });
    }
    
    // If you're not using Bootstrap's JS, you'll need this for dropdowns
    const mobileDropdowns = document.querySelectorAll('.mobile-dropdown > a');
    
    mobileDropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuId = this.getAttribute('data-bs-target');
            const submenu = document.querySelector(submenuId);
            
            if (submenu) {
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                } else {
                    // Close other open submenus first
                    document.querySelectorAll('.mobile-dropdown ul.show').forEach(menu => {
                        if (menu.id !== submenuId.replace('#', '')) {
                            menu.classList.remove('show');
                        }
                    });
                    submenu.classList.add('show');
                }
            }
        });
    });
});
    </script>
    @stack('scripts')
    
</body>

</html>

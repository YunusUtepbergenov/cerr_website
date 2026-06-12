<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        (function () {
            try {
                var t = localStorage.getItem('cerr-admin-theme')
                    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-admin-theme', t);
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>
    <title>{{ $title ?? __('admin.dashboard.title') }} — CERR Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins/fontawesome-5.css') }}">
    @vite(['resources/css/admin.css'])
    @stack('styles')
</head>
<body>
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <div class="admin-shell">
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="brand">
                <span class="brand-mark">C</span>
                <span>CERR Admin</span>
            </div>

            <div class="nav-label">{{ __('admin.nav.overview') }}</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> {{ __('admin.nav.dashboard') }}
            </a>

            <div class="nav-label">{{ __('admin.nav.content') }}</div>
            <a href="{{ route('admin.news.index') }}" class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                <i class="fa-solid fa-newspaper"></i> {{ __('admin.nav.news') }}
            </a>
            @if (auth()->user()?->canManageContent())
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open"></i> {{ __('admin.nav.categories') }}
            </a>
            <a href="{{ route('admin.tags.index') }}" class="{{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                <i class="fa-solid fa-tags"></i> {{ __('admin.nav.tags') }}
            </a>
            <a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-lines"></i> {{ __('admin.nav.pages') }}
            </a>
            <a href="{{ route('admin.videos.index') }}" class="{{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
                <i class="fa-solid fa-video"></i> {{ __('admin.nav.videos') }}
            </a>
            @endif
            <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                <i class="fa-solid fa-photo-film"></i> {{ __('admin.nav.media') }}
            </a>

            <div class="nav-label">{{ __('admin.nav.administration') }}</div>
            @if (auth()->user()?->canManageUsers())
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> {{ __('admin.nav.users') }}
            </a>
            @endif
            @if (auth()->user()?->canViewActivity())
            <a href="{{ route('admin.activity.index') }}" class="{{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clock-rotate-left"></i> {{ __('admin.nav.activity') }}
            </a>
            @endif

            <div class="sidebar-footer">
                v1.0 · {{ now()->format('Y') }} CERR
            </div>
        </aside>

        <div class="admin-main">
            <div class="admin-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="icon-btn sidebar-toggle" id="sidebar-toggle" aria-label="Toggle navigation">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <nav class="breadcrumb-trail" aria-label="breadcrumb">
                        <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-house"></i></a>
                        @if (! request()->routeIs('admin.dashboard'))
                            <span>/</span>
                            <span class="current">{{ $title ?? 'Page' }}</span>
                        @endif
                    </nav>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="icon-btn" id="theme-toggle" title="{{ __('admin.nav.toggle_theme') }}" aria-label="{{ __('admin.nav.toggle_theme') }}">
                        <i class="fa-solid fa-moon theme-icon-dark"></i>
                        <i class="fa-solid fa-sun theme-icon-light"></i>
                    </button>
                    <a href="{{ route('home') }}" target="_blank" class="icon-btn" title="{{ __('admin.nav.view_site') }}">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="user-chip">
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                        <span class="small text-muted">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="icon-btn" title="{{ __('admin.nav.sign_out') }}">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>

            <main class="admin-content">
                @if (session('status'))
                    <div class="toast-stack">
                        <div class="toast-item success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)">
                            <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-admin.confirm-modal />

    <script src="{{ asset('js/vendor/jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @vite(['resources/js/admin.js'])
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('admin.dashboard.title') }} — CERR Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins/fontawesome-5.css') }}">
    <style>
        :root {
            --admin-bg: #ffffff;
            --admin-surface: #ffffff;
            --admin-surface-soft: #fafafa;
            --admin-border: #e5e5e5;
            --admin-border-soft: #f0f0f0;
            --admin-text: #0a0a0a;
            --admin-text-muted: #737373;
            --admin-text-faint: #a3a3a3;
            --admin-primary: #5e6ad2;
            --admin-primary-hover: #4f5bc7;
            --admin-primary-soft: rgba(94, 106, 210, .08);
            --admin-success: #15803d;
            --admin-success-soft: #f0fdf4;
            --admin-danger: #b91c1c;
            --admin-danger-soft: #fef2f2;
            --admin-warning: #b45309;
            --admin-warning-soft: #fffbeb;
            --radius-sm: 5px;
            --radius: 7px;
            --radius-lg: 10px;
        }
        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html, body { background: var(--admin-bg); color: var(--admin-text); font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14.5px; line-height: 1.5; }
        a { color: var(--admin-primary); text-decoration: none; }
        a:hover { color: var(--admin-primary-hover); }
        h1, h2, h3, h4, h5 { color: var(--admin-text); font-weight: 600; letter-spacing: -.015em; }
        hr { border-color: var(--admin-border-soft); }
        code { font-family: 'JetBrains Mono', 'SF Mono', Menlo, monospace; font-size: .82rem; color: var(--admin-text-muted); }

        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 232px; background: var(--admin-surface-soft); color: var(--admin-text-muted);
            padding: 1rem 0 1.5rem; flex-shrink: 0; position: sticky; top: 0; height: 100vh;
            display: flex; flex-direction: column; border-right: 1px solid var(--admin-border);
        }
        .admin-sidebar .brand {
            padding: 0 1rem 1rem; font-weight: 600; font-size: .95rem;
            color: var(--admin-text); display: flex; align-items: center; gap: .55rem;
        }
        .admin-sidebar .brand .brand-mark {
            width: 26px; height: 26px; background: var(--admin-primary);
            border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-size: .75rem; font-weight: 600; letter-spacing: -.02em;
        }
        .admin-sidebar .nav-label {
            padding: .9rem 1rem .25rem; font-size: .72rem; font-weight: 500; letter-spacing: .05em;
            text-transform: uppercase; color: var(--admin-text-faint);
        }
        .admin-sidebar a {
            display: flex; align-items: center; gap: .6rem; padding: .42rem 1rem; margin: 0 .4rem;
            color: var(--admin-text-muted); font-size: .9rem; font-weight: 500;
            border-radius: var(--radius-sm); transition: background .1s, color .1s;
        }
        .admin-sidebar a i { width: 16px; text-align: center; font-size: .9rem; opacity: .8; }
        .admin-sidebar a:hover { background: rgba(0, 0, 0, .04); color: var(--admin-text); }
        .admin-sidebar a.active {
            background: var(--admin-primary-soft); color: var(--admin-primary);
        }
        .admin-sidebar a.active i { opacity: 1; }
        .admin-sidebar .sidebar-footer {
            margin-top: auto; padding: 1rem 1rem 0; font-size: .72rem; color: var(--admin-text-faint);
        }

        .admin-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .admin-topbar {
            background: rgba(255, 255, 255, .8); padding: .65rem 1.5rem; border-bottom: 1px solid var(--admin-border);
            display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50;
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        }
        .admin-topbar .breadcrumb-trail { display: flex; align-items: center; gap: .5rem; font-size: .9rem; color: var(--admin-text-faint); }
        .admin-topbar .breadcrumb-trail a { color: var(--admin-text-faint); }
        .admin-topbar .breadcrumb-trail a:hover { color: var(--admin-text); }
        .admin-topbar .breadcrumb-trail .current { color: var(--admin-text); font-weight: 500; }
        .admin-topbar .user-chip {
            display: flex; align-items: center; gap: .5rem; padding: .25rem .6rem .25rem .25rem;
            border-radius: 999px; background: transparent;
        }
        .admin-topbar .user-chip .avatar {
            width: 24px; height: 24px; border-radius: 50%; background: var(--admin-primary);
            color: #fff; font-size: .7rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center;
        }
        .admin-topbar .icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: var(--radius-sm); color: var(--admin-text-muted);
            border: 0; background: transparent; transition: background .1s, color .1s;
        }
        .admin-topbar .icon-btn:hover { background: rgba(0, 0, 0, .04); color: var(--admin-text); }

        .admin-content { padding: 2rem 2rem 4rem; flex: 1; max-width: 1400px; width: 100%; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap; }
        .page-header h1 { font-size: 1.55rem; margin: 0; font-weight: 600; }
        .page-header .subtitle { color: var(--admin-text-muted); font-size: .9rem; margin-top: .25rem; font-weight: 400; }

        .card { border: 1px solid var(--admin-border); border-radius: var(--radius); box-shadow: none; background: var(--admin-surface); }
        .card .card-header { background: var(--admin-surface); border-bottom: 1px solid var(--admin-border-soft); padding: .85rem 1.1rem; font-weight: 500; font-size: .9rem; color: var(--admin-text); }
        .card .card-body { padding: 1.1rem; }
        .card .card-footer { background: var(--admin-surface); border-top: 1px solid var(--admin-border-soft); padding: .75rem 1.1rem; }

        .stat-card { padding: 1rem 1.1rem; }
        .stat-card .label { font-size: .78rem; color: var(--admin-text-muted); text-transform: none; letter-spacing: 0; font-weight: 500; }
        .stat-card .value { font-size: 1.6rem; font-weight: 600; margin-top: .2rem; letter-spacing: -.025em; color: var(--admin-text); }
        .stat-card .value.success { color: var(--admin-success); }
        .stat-card .value.muted { color: var(--admin-text-faint); }

        .form-label { font-weight: 500; font-size: .85rem; color: var(--admin-text); margin-bottom: .35rem; }
        .form-text { color: var(--admin-text-faint); font-size: .8rem; }
        .form-control, .form-select {
            font-size: .9rem; padding: .5rem .7rem; border-radius: var(--radius-sm);
            border-color: var(--admin-border); color: var(--admin-text); background: var(--admin-surface);
            box-shadow: none; transition: border-color .1s, box-shadow .1s;
        }
        .form-control:focus, .form-select:focus { border-color: var(--admin-primary); box-shadow: 0 0 0 3px var(--admin-primary-soft); }
        .form-control::placeholder { color: var(--admin-text-faint); }
        .form-check-input { border-color: var(--admin-border); }
        .form-check-input:checked { background-color: var(--admin-primary); border-color: var(--admin-primary); }
        .form-check-input:focus { border-color: var(--admin-primary); box-shadow: 0 0 0 3px var(--admin-primary-soft); }
        .form-check-label { font-size: .9rem; color: var(--admin-text); }

        .btn {
            font-size: .9rem; font-weight: 500; padding: .48rem .9rem; border-radius: var(--radius-sm);
            border: 1px solid transparent; transition: background .1s, border-color .1s, color .1s;
        }
        .btn-sm { font-size: .82rem; padding: .32rem .6rem; }
        .btn-primary { background: var(--admin-primary); border-color: var(--admin-primary); color: #fff; }
        .btn-primary:hover, .btn-primary:focus { background: var(--admin-primary-hover); border-color: var(--admin-primary-hover); color: #fff; }
        .btn-primary:focus-visible { box-shadow: 0 0 0 3px var(--admin-primary-soft); }

        .btn-outline-primary { border-color: var(--admin-border); color: var(--admin-text); background: transparent; }
        .btn-outline-primary:hover { background: var(--admin-primary-soft); border-color: var(--admin-primary-soft); color: var(--admin-primary); }

        .btn-outline-secondary { border-color: var(--admin-border); color: var(--admin-text); background: transparent; }
        .btn-outline-secondary:hover { background: rgba(0, 0, 0, .04); border-color: var(--admin-border); color: var(--admin-text); }

        .btn-outline-danger { border-color: var(--admin-border); color: var(--admin-text-muted); background: transparent; }
        .btn-outline-danger:hover { background: var(--admin-danger-soft); border-color: var(--admin-danger-soft); color: var(--admin-danger); }

        .btn-success { background: var(--admin-success); border-color: var(--admin-success); color: #fff; }
        .btn-success:hover, .btn-success:focus { background: #166534; border-color: #166534; color: #fff; }

        .btn-danger { background: var(--admin-danger); border-color: var(--admin-danger); color: #fff; }
        .btn-danger:hover, .btn-danger:focus { background: #991b1b; border-color: #991b1b; color: #fff; }

        .btn-light { background: rgba(255,255,255,.9); border-color: var(--admin-border); color: var(--admin-text); }

        .table { color: var(--admin-text); font-size: .9rem; margin: 0; }
        .table > :not(caption) > * > * { vertical-align: middle; padding: .75rem 1rem; }
        .table > thead > tr > th { background: transparent; font-weight: 500; font-size: .78rem; color: var(--admin-text-faint); text-transform: none; letter-spacing: 0; border-bottom: 1px solid var(--admin-border); padding-top: .65rem; padding-bottom: .65rem; }
        .table > tbody > tr { border-bottom: 1px solid var(--admin-border-soft); transition: background .08s; }
        .table > tbody > tr:hover { background: var(--admin-surface-soft); }
        .table > tbody > tr:last-child { border-bottom: none; }

        .pill {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .2rem .55rem; font-size: .75rem; font-weight: 500; border-radius: var(--radius-sm);
            text-transform: none; letter-spacing: 0;
        }
        .pill::before { content: ''; width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .pill.status-draft { background: #f5f5f5; color: var(--admin-text-muted); }
        .pill.status-published { background: var(--admin-success-soft); color: var(--admin-success); }
        .pill.status-auto_publish { background: var(--admin-primary-soft); color: var(--admin-primary); }
        .pill.status-disabled { background: var(--admin-danger-soft); color: var(--admin-danger); }

        .lang-chip { display: inline-flex; padding: .12rem .42rem; font-size: .72rem; font-weight: 500; border-radius: 3px; background: #f5f5f5; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: .03em; margin-right: .2rem; font-feature-settings: "tnum"; }
        .lang-chip.missing { background: transparent; color: var(--admin-text-faint); border: 1px dashed var(--admin-border); }

        .lang-tabs { border-bottom: 1px solid var(--admin-border); margin-bottom: 1.25rem; gap: 0; }
        .lang-tabs .nav-link {
            color: var(--admin-text-muted); border: none; border-bottom: 2px solid transparent;
            padding: .6rem 1rem; margin-bottom: -1px; font-weight: 500; background: transparent; border-radius: 0; font-size: .9rem;
        }
        .lang-tabs .nav-link:hover { color: var(--admin-text); }
        .lang-tabs .nav-link.active { color: var(--admin-text); border-bottom-color: var(--admin-text); font-weight: 600; background: transparent; }
        .lang-tabs .nav-link .lang-status { font-size: .7rem; margin-left: .35rem; }

        .alert { border: 1px solid var(--admin-border-soft); border-radius: var(--radius-sm); padding: .65rem .9rem; font-size: .9rem; }
        .alert-success { background: var(--admin-success-soft); color: var(--admin-success); border-color: transparent; }
        .alert-danger { background: var(--admin-danger-soft); color: var(--admin-danger); border-color: transparent; }

        .toast-stack { position: fixed; top: 1rem; right: 1rem; z-index: 1080; display: flex; flex-direction: column; gap: .5rem; }
        .toast-item {
            background: var(--admin-surface); color: var(--admin-text); padding: .65rem .9rem; border-radius: var(--radius-sm);
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08), 0 1px 3px rgba(0, 0, 0, .04);
            border: 1px solid var(--admin-border); border-left: 3px solid var(--admin-text);
            font-size: .9rem; min-width: 240px;
            animation: toast-in .2s ease-out;
        }
        .toast-item.success { border-left-color: var(--admin-success); color: var(--admin-success); }
        @keyframes toast-in { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }

        .empty-state { padding: 3rem 1rem; text-align: center; color: var(--admin-text-muted); }
        .empty-state i { font-size: 1.85rem; opacity: .4; margin-bottom: .7rem; color: var(--admin-text-faint); }
        .empty-state .fw-semibold { color: var(--admin-text); font-weight: 500; }

        .sticky-action-bar {
            position: sticky; bottom: 0; background: rgba(255,255,255,.92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid var(--admin-border-soft); padding: .65rem 1rem; margin: 1rem -1rem -1rem;
            border-radius: 0 0 var(--radius) var(--radius); display: flex; justify-content: flex-end; gap: .5rem;
        }

        .pagination { margin: 0; gap: .15rem; }
        .pagination .page-link { color: var(--admin-text-muted); border-color: var(--admin-border); font-size: .85rem; padding: .35rem .65rem; border-radius: var(--radius-sm) !important; background: transparent; }
        .pagination .page-link:hover { background: rgba(0, 0, 0, .04); color: var(--admin-text); border-color: var(--admin-border); }
        .pagination .page-item.active .page-link { background: var(--admin-text); border-color: var(--admin-text); color: #fff; }
        .pagination .page-item.disabled .page-link { color: var(--admin-text-faint); background: transparent; }

        .btn-group .btn { border-radius: var(--radius-sm); }
        .btn-group > .btn:not(:last-child):not(.dropdown-toggle), .btn-group > .btn-group:not(:last-child) > .btn { border-top-right-radius: 0; border-bottom-right-radius: 0; }
        .btn-group > .btn:not(:first-child), .btn-group > .btn-group:not(:first-child) > .btn { border-top-left-radius: 0; border-bottom-left-radius: 0; margin-left: -1px; }

        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details > summary { color: var(--admin-text-muted); }
        details[open] > summary { color: var(--admin-text); }

        @media (max-width: 991px) {
            .admin-sidebar { position: fixed; left: -260px; transition: left .2s; z-index: 1050; box-shadow: 0 0 30px rgba(0,0,0,.1); }
            .admin-sidebar.is-open { left: 0; }
            .sidebar-toggle { display: inline-flex !important; }
            .admin-content { padding: 1.25rem; }
        }
        .sidebar-toggle { display: none; }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, .35); z-index: 1049; }
        .sidebar-backdrop.is-open { display: block; }
    </style>
    @stack('styles')
</head>
<body x-data="{ sidebar: false }">
    <div class="sidebar-backdrop" :class="{ 'is-open': sidebar }" @click="sidebar = false"></div>

    <div class="admin-shell">
        <aside class="admin-sidebar" :class="{ 'is-open': sidebar }">
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
            <a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
                <i class="fa-solid fa-photo-film"></i> {{ __('admin.nav.media') }}
            </a>

            <div class="nav-label">{{ __('admin.nav.administration') }}</div>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> {{ __('admin.nav.users') }}
            </a>
            <a href="{{ route('admin.activity.index') }}" class="{{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
                <i class="fa-solid fa-clock-rotate-left"></i> {{ __('admin.nav.activity') }}
            </a>

            <div class="sidebar-footer">
                v1.0 · {{ now()->format('Y') }} CERR
            </div>
        </aside>

        <div class="admin-main">
            <div class="admin-topbar">
                <div class="d-flex align-items-center gap-3">
                    <button type="button" class="icon-btn sidebar-toggle" @click="sidebar = !sidebar" aria-label="Toggle navigation">
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
    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin' }} — CERR Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins/fontawesome-5.css') }}">
    <style>
        :root {
            --admin-bg: #f7f8fb;
            --admin-surface: #ffffff;
            --admin-border: #e5e7eb;
            --admin-border-soft: #eef0f4;
            --admin-text: #111827;
            --admin-text-muted: #6b7280;
            --admin-primary: #2563eb;
            --admin-primary-hover: #1d4ed8;
            --admin-sidebar-bg: #0f172a;
            --admin-sidebar-text: #cbd5e1;
            --admin-sidebar-text-strong: #f8fafc;
            --admin-sidebar-active-bg: rgba(37, 99, 235, .18);
            --admin-sidebar-active-text: #ffffff;
            --admin-sidebar-hover-bg: rgba(255, 255, 255, .06);
            --admin-shadow-sm: 0 1px 2px rgba(15, 23, 42, .04);
            --admin-shadow: 0 4px 18px rgba(15, 23, 42, .06);
            --radius-sm: 6px;
            --radius: 10px;
            --radius-lg: 14px;
        }
        * { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
        html, body { background: var(--admin-bg); color: var(--admin-text); font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14.5px; }
        a { color: var(--admin-primary); text-decoration: none; }
        a:hover { color: var(--admin-primary-hover); }
        h1, h2, h3, h4, h5 { color: var(--admin-text); font-weight: 600; letter-spacing: -.01em; }

        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 248px; background: var(--admin-sidebar-bg); color: var(--admin-sidebar-text);
            padding: 1.25rem 0 2rem; flex-shrink: 0; position: sticky; top: 0; height: 100vh;
            display: flex; flex-direction: column;
        }
        .admin-sidebar .brand {
            padding: 0 1.5rem 1.25rem; font-weight: 700; font-size: 1.05rem;
            color: var(--admin-sidebar-text-strong); display: flex; align-items: center; gap: .6rem;
        }
        .admin-sidebar .brand .brand-mark {
            width: 30px; height: 30px; background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-size: .8rem; font-weight: 700;
        }
        .admin-sidebar .nav-label {
            padding: 1rem 1.5rem .35rem; font-size: .7rem; font-weight: 600; letter-spacing: .08em;
            text-transform: uppercase; color: #64748b;
        }
        .admin-sidebar a {
            display: flex; align-items: center; gap: .7rem; padding: .55rem 1.5rem;
            color: var(--admin-sidebar-text); font-size: .9rem; font-weight: 500;
            border-left: 2px solid transparent; transition: background .12s, color .12s, border-color .12s;
        }
        .admin-sidebar a i { width: 18px; text-align: center; opacity: .85; }
        .admin-sidebar a:hover { background: var(--admin-sidebar-hover-bg); color: var(--admin-sidebar-text-strong); }
        .admin-sidebar a.active {
            background: var(--admin-sidebar-active-bg); color: var(--admin-sidebar-active-text);
            border-left-color: var(--admin-primary);
        }
        .admin-sidebar .sidebar-footer {
            margin-top: auto; padding: 1rem 1.5rem 0; font-size: .75rem; color: #475569;
        }

        .admin-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .admin-topbar {
            background: var(--admin-surface); padding: .75rem 1.75rem; border-bottom: 1px solid var(--admin-border);
            display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 50;
            backdrop-filter: blur(8px);
        }
        .admin-topbar .breadcrumb-trail { display: flex; align-items: center; gap: .5rem; font-size: .9rem; color: var(--admin-text-muted); }
        .admin-topbar .breadcrumb-trail .current { color: var(--admin-text); font-weight: 600; }
        .admin-topbar .user-chip {
            display: flex; align-items: center; gap: .55rem; padding: .35rem .6rem .35rem .35rem;
            border-radius: 999px; background: var(--admin-bg); border: 1px solid var(--admin-border);
        }
        .admin-topbar .user-chip .avatar {
            width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #2563eb);
            color: #fff; font-size: .75rem; font-weight: 600; display: inline-flex; align-items: center; justify-content: center;
        }
        .admin-topbar .icon-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 8px; color: var(--admin-text-muted);
            border: 1px solid transparent; background: transparent;
        }
        .admin-topbar .icon-btn:hover { background: var(--admin-bg); color: var(--admin-text); }

        .admin-content { padding: 1.75rem; flex: 1; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.25rem; gap: 1rem; flex-wrap: wrap; }
        .page-header h1 { font-size: 1.5rem; margin: 0; }
        .page-header .subtitle { color: var(--admin-text-muted); font-size: .9rem; margin-top: .25rem; }

        .card { border: 1px solid var(--admin-border); border-radius: var(--radius); box-shadow: var(--admin-shadow-sm); background: var(--admin-surface); }
        .card .card-header { background: var(--admin-surface); border-bottom: 1px solid var(--admin-border-soft); padding: .85rem 1.1rem; font-weight: 600; }
        .card .card-body { padding: 1.1rem; }
        .card .card-footer { background: var(--admin-surface); border-top: 1px solid var(--admin-border-soft); padding: .75rem 1.1rem; }

        .stat-card { padding: 1rem 1.15rem; }
        .stat-card .label { font-size: .78rem; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }
        .stat-card .value { font-size: 1.65rem; font-weight: 700; margin-top: .25rem; letter-spacing: -.02em; }
        .stat-card .value.success { color: #16a34a; }
        .stat-card .value.muted { color: var(--admin-text-muted); }

        .form-label { font-weight: 500; font-size: .85rem; color: #374151; margin-bottom: .35rem; }
        .form-control, .form-select { font-size: .9rem; padding: .5rem .7rem; border-radius: var(--radius-sm); border-color: var(--admin-border); }
        .form-control:focus, .form-select:focus { border-color: var(--admin-primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
        .form-check-label { font-size: .9rem; }

        .btn { font-size: .9rem; font-weight: 500; padding: .5rem .9rem; border-radius: var(--radius-sm); }
        .btn-sm { font-size: .8rem; padding: .35rem .6rem; }
        .btn-primary { background: var(--admin-primary); border-color: var(--admin-primary); }
        .btn-primary:hover, .btn-primary:focus { background: var(--admin-primary-hover); border-color: var(--admin-primary-hover); }
        .btn-outline-primary { border-color: var(--admin-primary); color: var(--admin-primary); }
        .btn-outline-primary:hover { background: var(--admin-primary); border-color: var(--admin-primary); color: #fff; }

        .table { color: var(--admin-text); font-size: .9rem; margin: 0; }
        .table > :not(caption) > * > * { vertical-align: middle; padding: .8rem 1rem; }
        .table > thead > tr > th { background: #fafbfc; font-weight: 600; font-size: .78rem; color: var(--admin-text-muted); text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--admin-border); }
        .table > tbody > tr { border-bottom: 1px solid var(--admin-border-soft); transition: background .1s; }
        .table > tbody > tr:hover { background: #fafbfc; }
        .table > tbody > tr:last-child { border-bottom: none; }

        .pill {
            display: inline-flex; align-items: center; gap: .3rem;
            padding: .2rem .55rem; font-size: .72rem; font-weight: 600; border-radius: 999px;
            text-transform: uppercase; letter-spacing: .03em;
        }
        .pill::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .pill.status-draft { background: #f1f5f9; color: #475569; }
        .pill.status-published { background: #dcfce7; color: #166534; }
        .pill.status-auto_publish { background: #dbeafe; color: #1d4ed8; }
        .pill.status-disabled { background: #fee2e2; color: #b91c1c; }

        .lang-chip { display: inline-flex; padding: .15rem .45rem; font-size: .7rem; font-weight: 600; border-radius: 4px; background: #eef2ff; color: #4338ca; text-transform: uppercase; letter-spacing: .04em; margin-right: .2rem; }
        .lang-chip.missing { background: #f1f5f9; color: #94a3b8; }

        .lang-tabs { border-bottom: 1px solid var(--admin-border); margin-bottom: 1.25rem; gap: .25rem; }
        .lang-tabs .nav-link {
            color: var(--admin-text-muted); border: none; border-bottom: 2px solid transparent;
            padding: .55rem 1rem; font-weight: 500; background: transparent; border-radius: 0; font-size: .9rem;
        }
        .lang-tabs .nav-link:hover { color: var(--admin-text); border-bottom-color: var(--admin-border); }
        .lang-tabs .nav-link.active { color: var(--admin-primary); border-bottom-color: var(--admin-primary); font-weight: 600; background: transparent; }
        .lang-tabs .nav-link .lang-status { font-size: .7rem; margin-left: .35rem; }

        .alert { border: 0; border-radius: var(--radius-sm); padding: .65rem .9rem; font-size: .9rem; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #b91c1c; }

        .toast-stack { position: fixed; top: 1rem; right: 1rem; z-index: 1080; display: flex; flex-direction: column; gap: .5rem; }
        .toast-item {
            background: #111827; color: #f9fafb; padding: .65rem .9rem; border-radius: var(--radius-sm);
            box-shadow: var(--admin-shadow); font-size: .88rem; min-width: 240px;
            animation: toast-in .25s ease-out;
        }
        .toast-item.success { background: #16a34a; }
        @keyframes toast-in { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

        .empty-state { padding: 3rem 1rem; text-align: center; color: var(--admin-text-muted); }
        .empty-state i { font-size: 2rem; opacity: .4; margin-bottom: .5rem; }

        .sticky-action-bar {
            position: sticky; bottom: 0; background: rgba(255,255,255,.92); backdrop-filter: blur(10px);
            border-top: 1px solid var(--admin-border); padding: .75rem 1.1rem; margin: 1rem -1.1rem -1.1rem;
            border-radius: 0 0 var(--radius) var(--radius); display: flex; justify-content: flex-end; gap: .5rem;
        }

        .pagination { margin: 0; }
        .pagination .page-link { color: var(--admin-text-muted); border-color: var(--admin-border-soft); font-size: .85rem; padding: .4rem .7rem; }
        .pagination .page-link:hover { background: var(--admin-bg); color: var(--admin-primary); }
        .pagination .page-item.active .page-link { background: var(--admin-primary); border-color: var(--admin-primary); color: #fff; }

        @media (max-width: 991px) {
            .admin-sidebar { position: fixed; left: -260px; transition: left .25s; z-index: 1050; }
            .admin-sidebar.is-open { left: 0; }
            .sidebar-toggle { display: inline-flex !important; }
            .admin-content { padding: 1rem; }
        }
        .sidebar-toggle { display: none; }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 1049; }
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

            <div class="nav-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>

            <div class="nav-label">Content</div>
            <a href="{{ route('admin.news.index') }}" class="{{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                <i class="fa-solid fa-newspaper"></i> News
            </a>
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fa-solid fa-folder-open"></i> Categories
            </a>
            <a href="{{ route('admin.tags.index') }}" class="{{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">
                <i class="fa-solid fa-tags"></i> Tags
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
                    <a href="{{ route('home') }}" target="_blank" class="icon-btn" title="View site">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                    <div class="user-chip">
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                        <span class="small text-muted">{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="icon-btn" title="Sign out">
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

    <script src="{{ asset('js/vendor/jquery.min.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>

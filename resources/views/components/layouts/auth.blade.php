@php app()->setLocale('ru'); @endphp
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('admin.auth.sign_in') }} — CERR</title>
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <style>
        body { background: #f5f6fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; color: #111827; }
        .auth-card { width: 100%; max-width: 420px; background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,.04); color: #111827; }
        .auth-card h1 { font-size: 1.4rem; margin-bottom: 1.25rem; text-align: center; color: #111827; }
        .auth-card .form-label { color: #374151; font-weight: 500; font-size: .9rem; margin-bottom: .35rem; }
        .auth-card .form-control { color: #111827; border-color: #e5e7eb; }
        .auth-card .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15); }
        .auth-card .form-check-label { color: #374151; }
        .auth-card .btn-primary { background: #2563eb; border-color: #2563eb; color: #fff; font-weight: 500; }
        .auth-card .btn-primary:hover, .auth-card .btn-primary:focus { background: #1d4ed8; border-color: #1d4ed8; color: #fff; }
        .auth-card .invalid-feedback { color: #b91c1c; font-size: .85rem; }
        .auth-brand { text-align: center; margin-bottom: 1rem; font-weight: 700; color: #1f2937; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-brand">CERR Admin</div>
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>

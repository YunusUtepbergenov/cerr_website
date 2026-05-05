<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sign in' }} — CERR</title>
    <link rel="stylesheet" href="{{ asset('css/vendor/bootstrap.min.css') }}">
    <style>
        body { background: #f5f6fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif; }
        .auth-card { width: 100%; max-width: 420px; background: #fff; border: 1px solid #e5e7eb; border-radius: .75rem; padding: 2rem; box-shadow: 0 4px 20px rgba(0,0,0,.04); }
        .auth-card h1 { font-size: 1.4rem; margin-bottom: 1.25rem; text-align: center; }
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

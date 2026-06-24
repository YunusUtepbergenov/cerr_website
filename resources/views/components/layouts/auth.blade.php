<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? __('admin.auth.sign_in') }} — CERR</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --indigo: #5e6ad2;
            --indigo-hover: #4f5bc7;
            --violet: #8b5cf6;
            --grad: linear-gradient(135deg, #5e6ad2, #8b5cf6);
            --field: #121a3b;
            --field-border: rgba(255, 255, 255, .09);
            --text: #eef0f8;
            --text-dim: #9aa0bd;
            --ring: rgba(94, 106, 210, .35);
            --danger: #f87171;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            color: var(--text);
            background: linear-gradient(180deg, #0b1538 0%, #070c20 100%);
            min-height: 100svh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        /* Ambient deep-blue glow layers */
        .bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .bg .glow { position: absolute; border-radius: 50%; filter: blur(24px); }
        .bg .glow-a { top: -340px; left: 50%; transform: translateX(-50%); width: 900px; height: 900px; background: radial-gradient(circle at center, rgba(64, 100, 240, .5), rgba(64, 100, 240, 0) 62%); opacity: .6; }
        .bg .glow-b { bottom: -420px; right: -180px; width: 760px; height: 760px; background: radial-gradient(circle at center, rgba(139, 92, 246, .32), rgba(139, 92, 246, 0) 60%); opacity: .55; }
        .bg .glow-c { bottom: -360px; left: -220px; width: 660px; height: 660px; background: radial-gradient(circle at center, rgba(46, 92, 224, .28), rgba(46, 92, 224, 0) 60%); opacity: .6; }
        .bg .dots {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .05) 1px, transparent 1px);
            background-size: 26px 26px;
            -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 40%, #000 0%, transparent 78%);
            mask-image: radial-gradient(ellipse 70% 60% at 50% 40%, #000 0%, transparent 78%);
            opacity: .5;
        }

        .shell { position: relative; z-index: 1; width: 100%; max-width: 440px; }
        .card {
            background: linear-gradient(180deg, rgba(24, 33, 66, .92), rgba(15, 22, 47, .92));
            border: 1px solid var(--field-border);
            border-radius: 22px;
            padding: 40px 40px 30px;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, .06) inset,
                0 30px 70px -20px rgba(0, 0, 0, .7),
                0 10px 30px -15px rgba(94, 106, 210, .35);
            backdrop-filter: blur(8px);
        }

        .brand { display: flex; align-items: center; justify-content: center; margin-bottom: 24px; }
        .brand img { height: 40px; display: block; }

        .head { text-align: center; margin-bottom: 28px; }
        .head h1 { font-family: 'Manrope', sans-serif; font-weight: 800; font-size: 25px; letter-spacing: -.02em; line-height: 1.2; color: #fff; }
        .head p { margin-top: 8px; font-size: 14px; color: var(--text-dim); font-weight: 500; }

        form { display: flex; flex-direction: column; gap: 18px; }
        .field { display: flex; flex-direction: column; gap: 8px; }
        .field > label { font-size: 13px; font-weight: 600; color: var(--text-dim); letter-spacing: .01em; }

        .input-wrap { position: relative; display: flex; align-items: center; }
        .input-wrap input {
            width: 100%; height: 46px; padding: 0 14px;
            background: var(--field); border: 1px solid var(--field-border); border-radius: 10px;
            color: var(--text); font-family: inherit; font-size: 14.5px; font-weight: 500;
            transition: border-color .18s, box-shadow .18s, background .18s; outline: none;
        }
        .input-wrap input::placeholder { color: #5b6080; }
        .input-wrap input:hover { border-color: rgba(255, 255, 255, .16); }
        .input-wrap input:focus { border-color: var(--indigo); background: #141d40; box-shadow: 0 0 0 4px var(--ring); }
        .input-wrap input.is-invalid { border-color: var(--danger); }
        .input-wrap input.is-invalid:focus { box-shadow: 0 0 0 4px rgba(248, 113, 113, .2); }
        .input-wrap.has-toggle input { padding-right: 46px; }

        .toggle-pass {
            position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
            width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;
            background: transparent; border: 0; border-radius: 8px; color: var(--text-dim); cursor: pointer;
            transition: color .15s, background .15s;
        }
        .toggle-pass:hover { color: #cfd3ee; background: rgba(255, 255, 255, .06); }
        .toggle-pass svg { width: 17px; height: 17px; display: block; }

        .field-error { color: var(--danger); font-size: 12.5px; font-weight: 500; }

        .remember { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; align-self: flex-start; }
        .remember input { position: absolute; opacity: 0; width: 0; height: 0; }
        .remember .box { width: 18px; height: 18px; border-radius: 6px; border: 1px solid rgba(255, 255, 255, .18); background: var(--field); display: inline-flex; align-items: center; justify-content: center; transition: all .15s; flex: 0 0 auto; }
        .remember .box svg { width: 12px; height: 12px; opacity: 0; transform: scale(.6); transition: all .15s; color: #fff; }
        .remember input:checked + .box { background: var(--grad); border-color: transparent; }
        .remember input:checked + .box svg { opacity: 1; transform: scale(1); }
        .remember input:focus-visible + .box { box-shadow: 0 0 0 4px var(--ring); }
        .remember .lbl { font-size: 13.5px; color: var(--text-dim); font-weight: 500; }

        .submit {
            margin-top: 8px; height: 48px; width: 100%; border: 0; border-radius: 10px;
            background: var(--grad); color: #fff;
            font-family: 'Manrope', sans-serif; font-weight: 700; font-size: 15px; letter-spacing: .01em; cursor: pointer;
            box-shadow: 0 10px 24px -8px rgba(94, 106, 210, .7), 0 2px 6px rgba(0, 0, 0, .3);
            transition: transform .12s, box-shadow .18s, filter .18s;
        }
        .submit:hover { filter: brightness(1.06); box-shadow: 0 14px 30px -8px rgba(94, 106, 210, .8), 0 2px 6px rgba(0, 0, 0, .35); transform: translateY(-1px); }
        .submit:active { transform: translateY(0); filter: brightness(.98); }
        .submit:focus-visible { outline: none; box-shadow: 0 0 0 4px var(--ring), 0 10px 24px -8px rgba(94, 106, 210, .7); }

        .footer { margin-top: 24px; text-align: center; font-size: 12.5px; color: #5e6488; letter-spacing: .01em; }

        [x-cloak] { display: none !important; }

        @media (max-width: 480px) {
            .card { padding: 30px 22px 26px; border-radius: 18px; }
            .head h1 { font-size: 22px; }
            body { padding: 16px; }
        }
    </style>
</head>
<body>
    <div class="bg" aria-hidden="true">
        <div class="dots"></div>
        <div class="glow glow-a"></div>
        <div class="glow glow-b"></div>
        <div class="glow glow-c"></div>
    </div>

    <main class="shell">
        <section class="card">
            <div class="brand">
                <img src="{{ asset('images/logo.svg') }}" alt="CERR">
            </div>
            {{ $slot }}
        </section>
    </main>

    @livewireScripts
</body>
</html>

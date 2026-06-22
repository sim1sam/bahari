<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-site.meta :title="trim($__env->yieldContent('title')) ?: null" />
    <x-site.google-tag-manager location="head" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Instrument Sans', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f8fafc;
            color: #1c1917;
            -webkit-font-smoothing: antialiased;
        }

        .auth-brand {
            flex: 1;
            display: none;
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #0891b2 0%, #0e7490 45%, #155e75 100%);
            color: #fff;
            padding: 2.5rem 3rem;
        }

        @media (min-width: 992px) {
            .auth-brand { display: flex; flex-direction: column; justify-content: space-between; max-width: 45%; }
        }

        .auth-brand::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(255,255,255,.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,.08) 0%, transparent 40%);
            pointer-events: none;
        }

        .auth-brand::after {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            top: -120px;
            right: -100px;
            pointer-events: none;
        }

        .brand-inner { position: relative; z-index: 1; }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -.02em;
            color: #fff;
            text-decoration: none;
        }

        .brand-logo-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255,255,255,.18);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .brand-hero { margin-top: 3.5rem; max-width: 400px; }

        .brand-hero h2 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -.03em;
        }

        .brand-hero p {
            margin-top: 1rem;
            font-size: 1rem;
            line-height: 1.6;
            color: rgba(255,255,255,.82);
        }

        .brand-features {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: auto;
        }

        .brand-feature {
            display: flex;
            align-items: center;
            gap: .875rem;
            font-size: .9rem;
            color: rgba(255,255,255,.9);
        }

        .brand-feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,.14);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .brand-feature-icon svg { width: 16px; height: 16px; }

        .auth-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .mobile-logo {
            display: flex;
            align-items: center;
            gap: .625rem;
            padding: 1.5rem 1.5rem 0;
            font-size: 1.2rem;
            font-weight: 700;
            color: #0891b2;
            text-decoration: none;
        }

        @media (min-width: 992px) {
            .mobile-logo { display: none; }
        }

        .mobile-logo-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #ecfeff;
            color: #0891b2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .auth-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
        }

        .auth-form-wrap { width: 100%; max-width: 420px; }

        .auth-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem 1.75rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 8px 32px rgba(0,0,0,.06);
            border: 1px solid #f5f5f4;
        }

        @media (min-width: 480px) {
            .auth-card { padding: 2.25rem 2.25rem; }
        }

        .auth-header { margin-bottom: 2rem; }

        .auth-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -.02em;
            color: #1c1917;
        }

        .auth-header p {
            margin-top: .5rem;
            font-size: .9rem;
            color: #78716c;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .75rem 1rem;
            border-radius: 12px;
            font-size: .875rem;
            margin-bottom: 1.25rem;
        }

        .alert svg { width: 18px; height: 18px; flex-shrink: 0; }

        .alert-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .form-group { margin-bottom: 1.25rem; }

        .form-label {
            display: block;
            font-size: .8125rem;
            font-weight: 600;
            color: #44403c;
            margin-bottom: .5rem;
        }

        .input-wrap { position: relative; }

        .input-wrap svg {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: #a8a29e;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: .8rem 1rem .8rem 2.75rem;
            border: 1.5px solid #e7e5e4;
            border-radius: 12px;
            font-size: .9375rem;
            font-family: inherit;
            color: #1c1917;
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control::placeholder { color: #a8a29e; }

        .form-control:focus {
            outline: none;
            border-color: #0891b2;
            box-shadow: 0 0 0 3px rgba(8,145,178,.15);
            background: #fff;
        }

        .form-control.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239,68,68,.12);
        }

        .invalid-feedback {
            display: block;
            font-size: .8125rem;
            color: #dc2626;
            margin-top: .375rem;
        }

        .checkbox-row { margin-bottom: 1.5rem; }

        .checkbox-wrap {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            cursor: pointer;
            font-size: .875rem;
            color: #57534e;
            user-select: none;
        }

        .checkbox-wrap input {
            width: 16px;
            height: 16px;
            accent-color: #0891b2;
            cursor: pointer;
        }

        .btn-primary {
            width: 100%;
            padding: .875rem 1.5rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #0891b2, #0e7490);
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: transform .15s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(8,145,178,.35);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(8,145,178,.4);
        }

        .btn-primary:active { transform: translateY(0); }

        .auth-footer {
            margin-top: 1.75rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e7e5e4;
            text-align: center;
            font-size: .875rem;
            color: #78716c;
        }

        .auth-footer a {
            font-weight: 600;
            color: #0891b2;
            text-decoration: none;
        }

        .auth-footer a:hover { color: #0e7490; }

        .terms-note {
            font-size: .75rem;
            color: #78716c;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: 1.5rem;
            font-size: .875rem;
            color: #78716c;
            text-decoration: none;
            transition: color .2s;
        }

        .back-link:hover { color: #0891b2; }

        .back-link svg { width: 16px; height: 16px; }
    </style>
</head>
<body>
    <x-site.google-tag-manager location="body" />
    <aside class="auth-brand">
        <div class="brand-inner">
            <a href="{{ route('home') }}" class="brand-logo">
                @if ($site->logoUrl())
                    <img src="{{ $site->logoUrl() }}" alt="{{ $site->siteName() }}" style="height:44px;width:auto">
                @else
                    <span class="brand-logo-icon">{{ $site->logoInitial() }}</span>
                    {{ $site->siteName() }}
                @endif
            </a>
            <div class="brand-hero">
                @yield('brand_heading')
                @yield('brand_text')
            </div>
        </div>
        <div class="brand-features">
            @yield('brand_features')
        </div>
    </aside>

    <main class="auth-main">
        <a href="{{ route('home') }}" class="mobile-logo">
            @if ($site->logoUrl())
                <img src="{{ $site->logoUrl() }}" alt="{{ $site->siteName() }}" style="height:36px;width:auto">
            @else
                <span class="mobile-logo-icon">{{ $site->logoInitial() }}</span>
                {{ $site->siteName() }}
            @endif
        </a>

        <div class="auth-form-panel">
            <div class="auth-form-wrap">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>@yield('heading')</h1>
                        <p>@yield('subheading')</p>
                    </div>

                    @if (session('error'))
                        <div class="alert alert-danger">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')

                    <div class="auth-footer">
                        @yield('footer')
                    </div>
                </div>

                <a href="{{ route('home') }}" class="back-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to store
                </a>
            </div>
        </div>
    </main>
</body>
</html>

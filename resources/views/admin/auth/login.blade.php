<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-site.meta title="Admin Login" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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

        .login-brand {
            flex: 1;
            display: none;
            position: relative;
            overflow: hidden;
            background: linear-gradient(145deg, #0891b2 0%, #0e7490 45%, #155e75 100%);
            color: #fff;
            padding: 3rem;
        }

        @media (min-width: 992px) {
            .login-brand { display: flex; flex-direction: column; justify-content: space-between; }
        }

        .login-brand::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 80%, rgba(255,255,255,.12) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,.08) 0%, transparent 40%);
            pointer-events: none;
        }

        .login-brand::after {
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

        .brand-content { position: relative; z-index: 1; }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -.02em;
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
        }

        .brand-hero { margin-top: 4rem; max-width: 380px; }

        .brand-hero h1 {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -.03em;
            margin-bottom: 1rem;
        }

        .brand-hero p {
            font-size: 1.05rem;
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

        .brand-feature i {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,.14);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .85rem;
            flex-shrink: 0;
        }

        .login-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            min-height: 100vh;
        }

        .login-form-wrap {
            width: 100%;
            max-width: 420px;
        }

        .mobile-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .625rem;
            margin-bottom: 2rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #0891b2;
        }

        @media (min-width: 992px) {
            .mobile-logo { display: none; }
        }

        .mobile-logo-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #ecfeff;
            color: #0891b2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .login-header { margin-bottom: 2rem; }

        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            letter-spacing: -.02em;
            margin-bottom: .5rem;
        }

        .login-header p {
            color: #78716c;
            font-size: .95rem;
        }

        .alert {
            padding: .75rem 1rem;
            border-radius: 10px;
            font-size: .875rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: .5rem;
        }

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

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a8a29e;
            font-size: .9rem;
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
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control::placeholder { color: #a8a29e; }

        .form-control:focus {
            outline: none;
            border-color: #0891b2;
            box-shadow: 0 0 0 3px rgba(8,145,178,.15);
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

        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .checkbox-wrap {
            display: flex;
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

        .btn-signin {
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
            transition: transform .15s, box-shadow .2s, opacity .2s;
            box-shadow: 0 4px 14px rgba(8,145,178,.35);
        }

        .btn-signin:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(8,145,178,.4);
        }

        .btn-signin:active { transform: translateY(0); }

        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: 1.75rem;
            font-size: .875rem;
            color: #78716c;
            text-decoration: none;
            transition: color .2s;
        }

        .back-link:hover { color: #0891b2; }

        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,.04), 0 8px 32px rgba(0,0,0,.06);
            border: 1px solid #f5f5f4;
        }

        @media (min-width: 480px) {
            .login-card { padding: 2.5rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="login-brand">
        <div class="brand-content">
            <div class="brand-logo">
                @if ($site->logoUrl())
                    <img src="{{ $site->logoUrl() }}" alt="{{ $site->siteName() }}" style="height:44px;width:auto;border-radius:12px">
                @else
                    <span class="brand-logo-icon">{{ $site->logoInitial() }}</span>
                    {{ $site->siteName() }}
                @endif
            </div>
            <div class="brand-hero">
                <h1>Manage your store with ease</h1>
                <p>Track orders, update products, and keep your fashion catalog fresh — all from one dashboard.</p>
            </div>
        </div>
        <div class="brand-features">
            <div class="brand-feature">
                <i class="fas fa-box"></i>
                <span>Products &amp; categories management</span>
            </div>
            <div class="brand-feature">
                <i class="fas fa-shopping-bag"></i>
                <span>Real-time order tracking</span>
            </div>
            <div class="brand-feature">
                <i class="fas fa-chart-line"></i>
                <span>Sales overview &amp; analytics</span>
            </div>
        </div>
    </aside>

    <main class="login-form-panel">
        <div class="login-form-wrap">
            <div class="mobile-logo">
                @if ($site->logoUrl())
                    <img src="{{ $site->logoUrl() }}" alt="{{ $site->siteName() }}" style="height:36px;width:auto">
                @else
                    <span class="mobile-logo-icon"><i class="fas fa-store"></i></span>
                    {{ $site->siteName() }} Admin
                @endif
            </div>

            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome back</h2>
                    <p>Sign in to your admin account</p>
                </div>

                <form action="{{ route('admin.login.submit') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="email">Email address</label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope"></i>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="admin@ecommerce.com"
                                value="{{ old('email') }}"
                                required
                                autofocus
                            >
                        </div>
                        @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrap">
                            <i class="fas fa-lock"></i>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter your password"
                                required
                            >
                        </div>
                        @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-row">
                        <label class="checkbox-wrap">
                            <input type="checkbox" id="remember" name="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn-signin">
                        Sign in to dashboard
                    </button>
                </form>

                <a href="{{ route('home') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    Back to store
                </a>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-admin.flash-sweetalert />
    @stack('scripts')
</body>
</html>

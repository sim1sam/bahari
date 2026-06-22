<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-site.meta :title="(trim($__env->yieldContent('title')) ?: 'Dashboard').' Admin'" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <style>
        .main-sidebar .brand-link.admin-brand-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 0.85rem 0.75rem;
            min-height: 4.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            line-height: 1.2;
            white-space: normal;
            text-align: center;
        }

        .main-sidebar .brand-link.admin-brand-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .admin-sidebar-logo {
            display: block;
            max-height: 3.25rem;
            max-width: calc(100% - 0.5rem);
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .admin-sidebar-logo-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: #17a2b8;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .admin-sidebar-brand-text {
            display: block;
            max-width: 100%;
            font-size: 0.95rem;
            line-height: 1.25;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link.admin-brand-link,
        .sidebar-mini .main-sidebar .brand-link.admin-brand-link {
            justify-content: center;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:not(:hover) .brand-link.admin-brand-link {
            min-height: 3.5rem;
            padding: 0.65rem 0.35rem;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:not(:hover) .admin-sidebar-logo {
            max-height: 2rem;
            max-width: 2.5rem;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:not(:hover) .admin-sidebar-brand-text {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="{{ route('home') }}" target="_blank" class="nav-link">View Store</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link">{{ auth()->user()->name }}</span>
            </li>
            <li class="nav-item">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        @php
            $adminHomeRoute = \App\Support\AdminFeatures::firstAccessibleRoute(auth()->user()) ?? 'admin.dashboard';
        @endphp
        <a href="{{ route($adminHomeRoute) }}" class="brand-link admin-brand-link">
            <x-site.admin-logo />
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    @foreach (config('admin_features', []) as $key => $feature)
                        @if (auth()->user()->canAccessAdminFeature($key))
                            <li class="nav-item">
                                <a href="{{ route($feature['route']) }}" class="nav-link {{ request()->routeIs($feature['active']) ? 'active' : '' }}">
                                    <i class="nav-icon {{ $feature['icon'] }}"></i>
                                    <p>{{ $feature['label'] }}</p>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('page_title', 'Dashboard')</h1>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>{{ $site->siteName() }} Admin Panel</strong>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
@stack('scripts')
</body>
</html>

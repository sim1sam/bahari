<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#0891b2">
    <x-site.meta :title="(trim($__env->yieldContent('title')) ?: 'Dashboard').' Admin'" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .main-sidebar .brand-link.admin-brand-link {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 0;
            padding: 0 0.85rem;
            height: 3.5rem;
            min-height: 3.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            line-height: 1.2;
            white-space: nowrap;
            text-align: left;
            overflow: hidden;
        }

        .main-sidebar .brand-link.admin-brand-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .admin-sidebar-logo {
            display: block;
            flex: 0 0 auto;
            max-height: 2rem;
            max-width: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .admin-sidebar-logo-fallback {
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.45rem;
            background: #17a2b8;
            color: #fff;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .layout-fixed .main-header {
            height: 3.5rem;
        }

        .layout-fixed .main-header .navbar {
            height: 3.5rem;
            padding-top: 0;
            padding-bottom: 0;
        }

        .admin-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            line-height: 1.3;
            max-width: min(52vw, 28rem);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .content-header {
            display: none;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:hover .brand-link.admin-brand-link,
        .sidebar-mini .main-sidebar .brand-link.admin-brand-link {
            justify-content: center;
            padding: 0 0.5rem;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:not(:hover) .brand-link.admin-brand-link {
            height: 3.5rem;
            min-height: 3.5rem;
            padding: 0 0.35rem;
            justify-content: center;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:not(:hover) .admin-sidebar-logo {
            max-height: 1.75rem;
            max-width: 2.25rem;
        }

        .sidebar-mini.sidebar-collapse .main-sidebar:not(:hover) .admin-sidebar-logo-fallback {
            width: 1.75rem;
            height: 1.75rem;
            font-size: 0.75rem;
        }

        @media (max-width: 991.98px) {
            body.admin-mobile-app .main-footer {
                display: none;
            }

            body.admin-mobile-app .content-wrapper {
                padding-bottom: calc(4.75rem + env(safe-area-inset-bottom, 0px));
            }

            body.admin-mobile-app .main-header .navbar-nav .nav-item .nav-link span,
            body.admin-mobile-app .main-header form .nav-link {
                font-size: 0.85rem;
            }

            body.admin-mobile-app .admin-header-title {
                font-size: 1.05rem;
                max-width: min(42vw, 16rem);
            }

            body.admin-mobile-app .content {
                padding: 0.75rem 0.5rem;
            }
        }

        .admin-mobile-tabbar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1040;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            border-top: 1px solid #e5e7eb;
            padding-bottom: env(safe-area-inset-bottom, 0px);
            box-shadow: 0 -8px 24px rgba(15, 23, 42, 0.08);
        }

        .admin-mobile-tabbar-inner {
            display: grid;
            grid-template-columns: repeat(var(--tab-count, 5), minmax(0, 1fr));
            max-width: 32rem;
            margin: 0 auto;
            min-height: 4rem;
        }

        .admin-mobile-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.2rem;
            padding: 0.45rem 0.25rem;
            border: 0;
            background: transparent;
            color: #6b7280;
            font-size: 0.68rem;
            font-weight: 600;
            text-decoration: none;
            min-width: 0;
        }

        .admin-mobile-tab:hover,
        .admin-mobile-tab:focus {
            color: #0891b2;
            text-decoration: none;
            outline: none;
        }

        .admin-mobile-tab.is-active {
            color: #0891b2;
        }

        .admin-mobile-tab-icon {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: background 0.15s ease;
        }

        .admin-mobile-tab.is-active .admin-mobile-tab-icon {
            background: #ecfeff;
        }

        .admin-mobile-tab-label {
            line-height: 1.1;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-mobile-tab-badge {
            position: absolute;
            top: -0.15rem;
            right: -0.2rem;
            min-width: 1rem;
            height: 1rem;
            padding: 0 0.25rem;
            border-radius: 999px;
            background: #f59e0b;
            color: #fff;
            font-size: 0.62rem;
            font-weight: 700;
            line-height: 1rem;
        }

        .admin-more-modal .modal-dialog {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            margin: 0;
            max-width: none;
            transform: translateY(100%);
            transition: transform 0.25s ease;
        }

        .admin-more-modal.show .modal-dialog {
            transform: translateY(0);
        }

        .admin-more-modal .modal-content {
            border: 0;
            border-radius: 1.25rem 1.25rem 0 0;
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }

        .admin-more-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .admin-more-section-title:first-child {
            margin-top: 0 !important;
        }

        .admin-more-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-height: 5.5rem;
            padding: 0.75rem 0.5rem;
            border-radius: 0.9rem;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            color: #111827;
            font-size: 0.78rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
        }

        .admin-more-item:hover,
        .admin-more-item.is-active {
            color: #0891b2;
            background: #ecfeff;
            border-color: #a5f3fc;
            text-decoration: none;
        }

        .admin-more-item--button {
            padding: 0;
            border: 0;
            background: transparent;
        }

        .admin-more-logout-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            width: 100%;
            min-height: 5.5rem;
            padding: 0.75rem 0.5rem;
            border-radius: 0.9rem;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .admin-more-item-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.2rem;
            height: 2.2rem;
            border-radius: 0.7rem;
            background: #fff;
            font-size: 1rem;
        }

        /* —— Admin sidebar menu —— */
        .main-sidebar.admin-sidebar {
            background: linear-gradient(180deg, #0c1222 0%, #111827 48%, #0f3d47 100%);
            box-shadow: inset -1px 0 0 rgba(255, 255, 255, 0.06);
        }

        .admin-sidebar .sidebar {
            padding-bottom: 1rem;
        }

        .admin-sidebar .nav-sidebar {
            padding: 0.35rem 0.5rem 0.75rem;
        }

        .admin-sidebar .nav-sidebar > .nav-item {
            margin-bottom: 0.2rem;
        }

        .admin-sidebar .nav-sidebar .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            border-radius: 0.55rem;
            margin: 0.1rem 0.35rem;
            padding: 0.62rem 0.85rem;
            color: rgba(255, 255, 255, 0.82);
            transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        }

        .admin-sidebar .nav-sidebar .nav-link p {
            display: flex;
            align-items: center;
            flex: 1;
            margin: 0;
            padding-right: 1.35rem;
            font-weight: 500;
            letter-spacing: 0.01em;
        }

        .admin-sidebar .nav-sidebar .nav-icon {
            color: rgba(165, 243, 252, 0.9);
            font-size: 0.95rem;
            width: 1.6rem;
            margin-right: 0.55rem;
            text-align: center;
            flex-shrink: 0;
        }

        .admin-sidebar .nav-sidebar .nav-link:hover {
            background: rgba(8, 145, 178, 0.18);
            color: #fff;
        }

        .admin-sidebar .nav-sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(8, 145, 178, 0.35) 0%, rgba(8, 145, 178, 0.12) 100%);
            color: #fff;
            box-shadow: inset 3px 0 0 #22d3ee;
        }

        .admin-sidebar .admin-nav-parent {
            font-weight: 600;
        }

        .admin-sidebar .admin-nav-parent > p {
            font-weight: 600;
        }

        .admin-sidebar .nav-sidebar .nav-link > p > .right,
        .admin-sidebar .admin-nav-chevron {
            position: absolute;
            right: 0.85rem;
            top: 50%;
            margin-left: 0;
            margin-top: 0 !important;
            font-size: 0.7rem;
            opacity: 0.75;
            line-height: 1;
            transform: translateY(-50%);
            transition: transform 0.2s ease;
        }

        .admin-sidebar .menu-open > .nav-link .admin-nav-chevron {
            transform: translateY(-50%) rotate(180deg);
        }

        .admin-sidebar .admin-nav-submenu {
            margin: 0.15rem 0.35rem 0.35rem 1.15rem;
            padding: 0.2rem 0 0.25rem 0.65rem;
            border-left: 2px solid rgba(34, 211, 238, 0.28);
            background: rgba(0, 0, 0, 0.12);
            border-radius: 0 0.45rem 0.45rem 0;
        }

        .admin-sidebar .admin-nav-submenu .nav-item {
            margin-bottom: 0.05rem;
        }

        .admin-sidebar .admin-nav-child {
            padding: 0.48rem 0.65rem 0.48rem 0.75rem !important;
            margin: 0.05rem 0 !important;
            font-size: 0.84rem;
            color: rgba(255, 255, 255, 0.68) !important;
            border-radius: 0.4rem !important;
        }

        .admin-sidebar .admin-nav-child .nav-icon {
            font-size: 0.72rem;
            width: 1.15rem;
            margin-right: 0.45rem;
            opacity: 0.85;
            color: rgba(165, 243, 252, 0.65);
        }

        .admin-sidebar .admin-nav-child p {
            font-weight: 400;
            font-size: 0.84rem;
        }

        .admin-sidebar .admin-nav-child:hover {
            background: rgba(8, 145, 178, 0.14) !important;
            color: rgba(255, 255, 255, 0.95) !important;
        }

        .admin-sidebar .admin-nav-child.active {
            background: rgba(8, 145, 178, 0.22) !important;
            color: #fff !important;
            box-shadow: inset 2px 0 0 #22d3ee;
        }

        .admin-sidebar .admin-nav-child.active .nav-icon {
            color: #67e8f9;
        }

        .admin-sidebar .admin-nav-group.menu-open > .admin-nav-parent {
            background: rgba(8, 145, 178, 0.12);
            color: #e0f2fe;
        }

        .admin-sidebar .brand-link.admin-brand-link {
            background: rgba(0, 0, 0, 0.2);
            border-bottom-color: rgba(34, 211, 238, 0.15);
        }

        .sidebar-mini.sidebar-collapse .admin-sidebar:not(:hover) .admin-nav-submenu {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed admin-mobile-app">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0">
        <ul class="navbar-nav align-items-center">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item pl-2">
                <h1 class="admin-header-title">@yield('page_title', 'Dashboard')</h1>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item d-none d-md-inline-block">
                <span class="nav-link">{{ auth()->user()->name }}</span>
            </li>
            <li class="nav-item d-none d-md-inline-block">
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-link nav-link">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4 admin-sidebar">
        @php
            $adminHomeRoute = \App\Support\AdminFeatures::firstAccessibleRoute(auth()->user()) ?? 'admin.dashboard';
        @endphp
        <a href="{{ route($adminHomeRoute) }}" class="brand-link admin-brand-link">
            <x-site.admin-logo />
        </a>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    @foreach (\App\Support\AdminFeatures::navigationFor(auth()->user()) as $nav)
                        @if ($nav['type'] === 'item')
                            @php $feature = $nav['feature']; @endphp
                            <li class="nav-item">
                                <a href="{{ route($feature['route']) }}" class="nav-link admin-nav-single {{ \App\Support\AdminFeatures::isNavigationItemActive($feature) ? 'active' : '' }}">
                                    <i class="nav-icon {{ $feature['icon'] }}"></i>
                                    <p>{{ $feature['label'] }}</p>
                                </a>
                            </li>
                        @else
                            @php $groupActive = \App\Support\AdminFeatures::isNavigationGroupActive($nav['items']); @endphp
                            <li class="nav-item admin-nav-group has-treeview {{ $groupActive ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link admin-nav-parent {{ $groupActive ? 'active' : '' }}">
                                    <i class="nav-icon {{ $nav['icon'] }}"></i>
                                    <p>
                                        {{ $nav['label'] }}
                                        <i class="right fas fa-chevron-down admin-nav-chevron"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview admin-nav-submenu">
                                    @foreach ($nav['items'] as $item)
                                        @php $feature = $item['feature']; @endphp
                                        <li class="nav-item">
                                            <a href="{{ route($feature['route']) }}" class="nav-link admin-nav-child {{ \App\Support\AdminFeatures::isNavigationItemActive($feature) ? 'active' : '' }}">
                                                <i class="nav-icon {{ $feature['icon'] }}"></i>
                                                <p>{{ $feature['label'] }}</p>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer d-none d-lg-block">
        <strong>{{ $site->siteName() }} Admin Panel</strong>
    </footer>

    <x-admin.mobile-tab-bar />
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    document.querySelectorAll('#adminMoreMenuModal .admin-more-item[href]').forEach(function (link) {
        link.addEventListener('click', function () {
            if (typeof $ !== 'undefined') {
                $('#adminMoreMenuModal').modal('hide');
            }
        });
    });
})();
</script>
<x-admin.flash-sweetalert />
@stack('scripts')
</body>
</html>

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

            body.admin-mobile-app .content-header {
                padding: 0.75rem 0.5rem 0;
            }

            body.admin-mobile-app .content-header h1 {
                font-size: 1.35rem;
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
    </style>
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed admin-mobile-app">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-lg-inline-block">
                <a href="{{ route('home') }}" target="_blank" class="nav-link">View Store</a>
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

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
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
                                <a href="{{ route($feature['route']) }}" class="nav-link {{ \App\Support\AdminFeatures::isNavigationItemActive($feature) ? 'active' : '' }}">
                                    <i class="nav-icon {{ $feature['icon'] }}"></i>
                                    <p>{{ $feature['label'] }}</p>
                                </a>
                            </li>
                        @else
                            @php $groupActive = \App\Support\AdminFeatures::isNavigationGroupActive($nav['items']); @endphp
                            <li class="nav-item has-treeview {{ $groupActive ? 'menu-open' : '' }}">
                                <a href="#" class="nav-link {{ $groupActive ? 'active' : '' }}">
                                    <i class="nav-icon {{ $nav['icon'] }}"></i>
                                    <p>
                                        {{ $nav['label'] }}
                                        <i class="right fas fa-angle-left"></i>
                                    </p>
                                </a>
                                <ul class="nav nav-treeview">
                                    @foreach ($nav['items'] as $item)
                                        @php $feature = $item['feature']; @endphp
                                        <li class="nav-item">
                                            <a href="{{ route($feature['route']) }}" class="nav-link {{ \App\Support\AdminFeatures::isNavigationItemActive($feature) ? 'active' : '' }}">
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

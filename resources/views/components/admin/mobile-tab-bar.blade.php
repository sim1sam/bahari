@php
    use App\Support\AdminFeatures;

    $user = auth()->user();
    $features = AdminFeatures::all();
    $navigation = AdminFeatures::navigationFor($user);

    $primaryKeys = ['dashboard', 'orders', 'products', 'customers'];
    $tabs = [];

    foreach ($primaryKeys as $key) {
        if (! isset($features[$key]) || ! $user->canAccessAdminFeature($key)) {
            continue;
        }

        $feature = $features[$key];
        $tabs[] = [
            'key' => $key,
            'label' => match ($key) {
                'dashboard' => 'Home',
                'orders' => 'Orders',
                'products' => 'Products',
                'customers' => 'Customers',
                default => $feature['label'],
            },
            'icon' => $feature['icon'],
            'route' => $feature['route'],
            'active' => request()->routeIs($feature['active']),
        ];
    }

    $moreSections = [];
    $onPrimaryTab = collect($primaryKeys)->contains(function ($key) use ($features, $user) {
        if (! isset($features[$key]) || ! $user->canAccessAdminFeature($key)) {
            return false;
        }

        return request()->routeIs($features[$key]['active']);
    });

    foreach ($navigation as $nav) {
        if ($nav['type'] === 'item') {
            if (in_array($nav['key'], $primaryKeys, true)) {
                continue;
            }

            $moreSections[] = [
                'label' => null,
                'items' => [[
                    'key' => $nav['key'],
                    'label' => $nav['feature']['label'],
                    'icon' => $nav['feature']['icon'],
                    'route' => $nav['feature']['route'],
                    'active' => AdminFeatures::isNavigationItemActive($nav['feature']),
                ]],
            ];

            continue;
        }

        $sectionItems = [];

        foreach ($nav['items'] as $item) {
            if (in_array($item['key'], $primaryKeys, true)) {
                continue;
            }

            $sectionItems[] = [
                'key' => $item['key'],
                'label' => $item['feature']['label'],
                'icon' => $item['feature']['icon'],
                'route' => $item['feature']['route'],
                'active' => AdminFeatures::isNavigationItemActive($item['feature']),
            ];
        }

        if ($sectionItems !== []) {
            $moreSections[] = [
                'label' => $nav['label'],
                'items' => $sectionItems,
            ];
        }
    }

    $showMore = collect($moreSections)->flatMap(fn ($section) => $section['items'])->isNotEmpty();
    $pendingOrders = $user->canAccessAdminFeature('orders')
        ? \App\Models\Order::where('status', 'pending')->count()
        : 0;
@endphp

@if (count($tabs) > 0 || $showMore)
    @php $tabCount = count($tabs) + ($showMore ? 1 : 0); @endphp
    <nav class="admin-mobile-tabbar d-lg-none" aria-label="Admin navigation">
        <div class="admin-mobile-tabbar-inner" style="--tab-count: {{ $tabCount }}">
            @foreach ($tabs as $tab)
                <a
                    href="{{ route($tab['route']) }}"
                    class="admin-mobile-tab {{ $tab['active'] ? 'is-active' : '' }}"
                >
                    <span class="admin-mobile-tab-icon">
                        <i class="{{ $tab['icon'] }}"></i>
                        @if ($tab['key'] === 'orders' && $pendingOrders > 0)
                            <span class="admin-mobile-tab-badge">{{ $pendingOrders > 9 ? '9+' : $pendingOrders }}</span>
                        @endif
                    </span>
                    <span class="admin-mobile-tab-label">{{ $tab['label'] }}</span>
                </a>
            @endforeach

            @if ($showMore)
                <button
                    type="button"
                    class="admin-mobile-tab {{ ! $onPrimaryTab ? 'is-active' : '' }}"
                    data-toggle="modal"
                    data-target="#adminMoreMenuModal"
                    aria-label="More menu"
                >
                    <span class="admin-mobile-tab-icon"><i class="fas fa-th-large"></i></span>
                    <span class="admin-mobile-tab-label">More</span>
                </button>
            @endif
        </div>
    </nav>

    <div class="modal fade admin-more-modal d-lg-none" id="adminMoreMenuModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold">Menu</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body pt-2">
                    @foreach ($moreSections as $section)
                        @if ($section['label'])
                            <h6 class="admin-more-section-title text-muted text-uppercase small font-weight-bold mb-2 mt-3">{{ $section['label'] }}</h6>
                        @endif
                        <div class="admin-more-grid {{ $section['label'] ? 'mb-2' : '' }}">
                            @foreach ($section['items'] as $item)
                                <a href="{{ route($item['route']) }}" class="admin-more-item {{ $item['active'] ? 'is-active' : '' }}">
                                    <span class="admin-more-item-icon"><i class="{{ $item['icon'] }}"></i></span>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="admin-more-grid">
                        <form action="{{ route('admin.logout') }}" method="POST" class="admin-more-item admin-more-item--button">
                            @csrf
                            <button type="submit" class="admin-more-logout-btn">
                                <span class="admin-more-item-icon"><i class="fas fa-sign-out-alt"></i></span>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

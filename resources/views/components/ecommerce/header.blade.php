@php
    $navLinks = app(\App\Services\CategoryCatalog::class)->navigationLinks();
    $cartService = app(\App\Services\CartService::class);
    $productCatalog = app(\App\Services\ProductCatalog::class);
    $cartItems = collect($cartService->items())->map(function ($item) use ($productCatalog) {
        $product = $productCatalog->find($item['slug']);
        $item['available_sizes'] = $product['sizes'] ?? [];
        $item['size_hint'] = implode(', ', $item['available_sizes']);
        $item['product_url'] = route('products.show', $item['slug']);
        $item['update_url'] = route('cart.update', $item['key']);
        $item['remove_url'] = route('cart.remove', $item['key']);
        $item['line_total_formatted'] = money($item['price'] * $item['quantity']);
        $item['syncing'] = false;

        return $item;
    })->values()->all();
    $cartSubtotal = $cartService->subtotal();
    $cartShipping = $cartService->shipping();
    $cartDiscount = $cartService->discount();
    $cartTotal = $cartService->total();
    $siteSettings = app(\App\Services\SiteSettingsService::class);
    $freeShippingAt = $siteSettings->freeShippingThreshold();
    $freeShippingRemaining = max(0, $freeShippingAt - $cartSubtotal);
    $hideFloatingCart = request()->routeIs('checkout.*');
    $canUseCartDrawer = ! $hideFloatingCart;
    $accountUser = auth()->user();
    $accountLabel = $accountUser && ! $accountUser->isAdmin()
        ? (strtok($accountUser->name, ' ') ?: $accountUser->name)
        : null;
@endphp

<script>
    window.__CART_BOOT__ = {
        open: {{ session('cart_drawer_open') && $canUseCartDrawer ? 'true' : 'false' }},
        enabled: {{ $canUseCartDrawer ? 'true' : 'false' }},
        items: @json($cartItems),
        count: {{ (int) ($cartCount ?? 0) }},
        subtotal: @json(money($cartSubtotal)),
        discountAmount: {{ (float) $cartDiscount }},
        discount: @json(money($cartDiscount)),
        shipping: @json(money_or_free($cartShipping)),
        total: @json(money($cartTotal)),
        freeShippingRemaining: {{ (float) $freeShippingRemaining }},
        freeShippingRemainingFormatted: @json(money($freeShippingRemaining)),
    };
</script>

<header
    class="sticky top-0 z-50 bg-surface-elevated/95 backdrop-blur-md border-b border-border"
    x-data="{ mobileOpen: false, searchOpen: false }"
    @cart:add.window="if ($event.detail && $event.detail.form) $store.cart.addFromForm($event.detail.form)"
>
    <div class="container-store">
        <div class="flex items-center justify-between gap-4 py-4">
            <button
                type="button"
                class="lg:hidden p-2 -ml-2 text-ink-muted hover:text-ink transition-colors"
                @click="mobileOpen = !mobileOpen"
                aria-label="Toggle menu"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <x-site.logo :show-name="true" name-class="hidden sm:block" />

            <div class="hidden lg:flex flex-1 max-w-xl mx-8">
                <x-ecommerce.search-bar :query="request('q', '')" />
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
                <button
                    type="button"
                    class="lg:hidden p-2 text-ink-muted hover:text-ink transition-colors"
                    @click="searchOpen = !searchOpen"
                    aria-label="Search"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <a href="#" class="p-2 text-ink-muted hover:text-ink transition-colors hidden sm:block" aria-label="Wishlist">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </a>

                @if ($accountLabel)
                    <a
                        href="{{ route('account.dashboard') }}"
                        class="inline-flex items-center gap-1.5 px-2 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('account.*') ? 'text-brand-600 bg-brand-50' : 'text-ink-muted hover:text-brand-600 hover:bg-surface' }}"
                        aria-label="My Account"
                        title="{{ $accountUser->name }}"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="max-w-[7rem] truncate">{{ $accountLabel }}</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 p-2 text-ink-muted hover:text-ink transition-colors" aria-label="Sign in">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="hidden sm:inline text-sm font-medium">Sign In</span>
                    </a>
                @endif

                @if ($canUseCartDrawer)
                <button type="button" class="relative p-2 text-ink-muted hover:text-ink transition-colors" aria-label="Cart" @click="$store.cart.openDrawer()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="$store.cart.count > 0" class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-4 h-4 px-1 text-[10px] font-bold bg-brand-600 text-white rounded-full" x-text="$store.cart.count">{{ $cartCount ?? 0 }}</span>
                </button>
                @endif
            </div>
        </div>

        <div x-show="searchOpen" x-cloak class="lg:hidden pb-4">
            <x-ecommerce.search-bar
                :query="request('q', '')"
                placeholder="Search products..."
            />
        </div>

        <nav class="hidden lg:flex items-center gap-8 pb-4 border-t border-border pt-4 -mt-px">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="text-sm font-medium transition-colors {{ $link['active'] ? 'text-brand-600' : 'text-ink-muted hover:text-brand-600' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    <div
        x-show="mobileOpen"
        x-cloak
        class="lg:hidden border-t border-border bg-surface-elevated"
        @click.outside="mobileOpen = false"
    >
        <nav class="container-store py-4 flex flex-col gap-1">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $link['active'] ? 'bg-brand-50 text-brand-600' : 'text-ink-muted hover:bg-surface hover:text-brand-600' }}"
                    @click="mobileOpen = false"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
            @if ($accountLabel)
                <a href="{{ route('account.dashboard') }}" class="px-3 py-2.5 rounded-lg text-sm font-semibold text-brand-600 hover:bg-brand-50 transition-colors" @click="mobileOpen = false">
                    {{ $accountUser->name }}
                </a>
            @else
                <a href="{{ route('login') }}" class="px-3 py-2.5 rounded-lg text-sm font-medium text-ink-muted hover:bg-surface hover:text-brand-600 transition-colors" @click="mobileOpen = false">Sign In</a>
                <a href="{{ route('register') }}" class="px-3 py-2.5 rounded-lg text-sm font-semibold text-brand-600 hover:bg-brand-50 transition-colors" @click="mobileOpen = false">Register</a>
            @endif
        </nav>
    </div>

    @if ($canUseCartDrawer)
    <template x-teleport="body">
        <div x-data>
            <button
                type="button"
                x-show="!$store.cart.open"
                x-cloak
                class="fixed top-1/2 flex min-w-20 -translate-y-1/2 flex-col overflow-hidden rounded-xl bg-white text-xs font-semibold text-ink shadow-xl ring-1 ring-black/5"
                style="right: 0.5rem; z-index: 9998;"
                @click="$store.cart.openDrawer()"
                aria-label="Open cart"
            >
                <span class="flex w-full flex-col items-center justify-center gap-1 bg-brand-600 px-3 py-3 text-white hover:bg-brand-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13 5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z"/>
                    </svg>
                    <span class="whitespace-nowrap px-1"><span x-text="$store.cart.count">{{ $cartCount ?? 0 }}</span> Items</span>
                </span>
                <span class="w-full whitespace-nowrap px-3 py-2 text-center text-[10px] font-bold text-ink" x-text="$store.cart.subtotal">{{ money($cartSubtotal) }}</span>
            </button>

            <div x-show="$store.cart.open" x-cloak class="fixed inset-0" style="z-index: 9999;" aria-modal="true" role="dialog">
                <div class="absolute inset-0 bg-black/45" @click="$store.cart.closeDrawer()"></div>
                <aside class="fixed bottom-0 right-0 top-0 flex h-dvh min-h-dvh w-full max-w-sm flex-col bg-surface-elevated shadow-2xl" style="z-index: 10000;" @click.stop>
                    <div class="flex items-center justify-between border-b border-border px-4 py-3">
                        <h2 class="text-base font-bold text-ink">Your Cart</h2>
                        <button type="button" class="rounded-full bg-surface p-1.5 text-ink-muted hover:text-ink" @click="$store.cart.closeDrawer()" aria-label="Close cart">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 pb-2">
                        <div x-show="$store.cart.items.length > 0">
                            <div class="mb-3">
                                <p class="text-sm font-semibold text-ink"><span x-text="$store.cart.count">{{ $cartCount ?? 0 }}</span> Item<span x-show="$store.cart.count !== 1">s</span></p>
                                <p x-show="$store.cart.freeShippingRemaining > 0" class="mt-1 text-[11px] text-brand-600">Add <span x-text="$store.cart.freeShippingRemainingFormatted">{{ money($freeShippingRemaining) }}</span> more to get free shipping.</p>
                            </div>

                            <div class="space-y-3">
                                <template x-for="item in $store.cart.items" :key="item.key">
                                    <div class="flex gap-3 border-b border-border pb-3 last:border-b-0">
                                        <a :href="item.product_url" class="h-16 w-12 shrink-0 overflow-hidden rounded-lg border border-border bg-brand-50">
                                            <img :src="item.image" :alt="item.name" class="h-full w-full object-cover object-top">
                                        </a>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <a :href="item.product_url" class="line-clamp-2 text-xs font-semibold leading-snug text-ink hover:text-brand-600" x-text="item.name"></a>
                                                <form :action="item.remove_url" method="POST" class="shrink-0" @submit.prevent="$store.cart.removeItem($el, item)">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="cart_drawer" value="1">
                                                    <button type="submit" class="text-xs text-ink-muted hover:text-red-600" aria-label="Remove item">×</button>
                                                </form>
                                            </div>

                                            <form :action="item.update_url" method="POST" class="mt-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="cart_drawer" value="1">
                                                <input type="hidden" name="quantity" :value="item.quantity">

                                                <div class="flex items-end justify-between gap-2">
                                                    <div>
                                                        <label class="mb-1 block text-[10px] font-medium text-ink-muted">Size</label>
                                                        <input
                                                            type="text"
                                                            name="size"
                                                            :value="item.size"
                                                            :placeholder="item.size_hint || 'Size'"
                                                            @change="$store.cart.updateItem($el.form, item, item.quantity)"
                                                            class="h-8 w-20 rounded-md border border-border bg-surface px-2 text-[11px] text-ink placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                                                        >
                                                    </div>

                                                    <div class="flex items-center gap-2">
                                                        <p class="text-xs font-semibold text-ink" x-text="item.line_total_formatted"></p>
                                                        <div class="flex h-9 items-center overflow-hidden rounded-full border border-border bg-surface">
                                                            <button
                                                                type="button"
                                                                @click="if (item.quantity > 1 && ! item.syncing) { $store.cart.updateItem($el.form, item, item.quantity - 1); }"
                                                                class="flex h-full w-9 items-center justify-center text-sm text-ink-muted hover:bg-surface-elevated"
                                                            >-</button>
                                                            <span class="w-8 text-center text-sm font-semibold text-ink" x-text="item.quantity"></span>
                                                            <button
                                                                type="button"
                                                                @click="if (item.quantity < 10 && ! item.syncing) { $store.cart.updateItem($el.form, item, item.quantity + 1); }"
                                                                class="flex h-full w-9 items-center justify-center text-sm bg-brand-600 text-white hover:bg-brand-700"
                                                            >+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="$store.cart.items.length === 0" class="flex h-full flex-col items-center justify-center text-center">
                            <div class="mb-4 rounded-full bg-brand-50 p-4 text-brand-600">
                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"/></svg>
                            </div>
                            <p class="font-semibold text-ink">Your cart is empty</p>
                            <p class="mt-1 text-sm text-ink-muted">Add products to see them here.</p>
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-border bg-surface-elevated px-4 pb-[calc(1rem+env(safe-area-inset-bottom,0))] pt-3">
                        <h3 class="mb-2 text-sm font-bold text-ink">Order Summary</h3>
                        <div class="mb-1.5 flex items-center justify-between text-xs">
                            <span class="text-ink-muted">Sub Total (<span x-text="$store.cart.count">{{ $cartCount ?? 0 }}</span> item<span x-show="$store.cart.count !== 1">s</span>)</span>
                            <span class="font-medium text-ink" x-text="$store.cart.subtotal">{{ money($cartSubtotal) }}</span>
                        </div>
                        <div class="mb-1.5 flex items-center justify-between text-xs" x-show="$store.cart.discountAmount > 0">
                            <span class="text-ink-muted">Discount</span>
                            <span class="font-medium text-brand-600" x-text="$store.cart.discount">{{ money($cartDiscount) }}</span>
                        </div>
                        <div class="mb-2 flex items-center justify-between border-b border-border pb-2 text-xs">
                            <span class="text-ink-muted">Shipping</span>
                            <span class="font-medium text-ink" x-text="$store.cart.shipping">{{ money_or_free($cartShipping) }}</span>
                        </div>
                        <div class="mb-3 flex items-center justify-between text-sm">
                            <span class="font-bold text-ink">Total Amount</span>
                            <span class="font-bold text-ink" x-text="$store.cart.total">{{ money($cartTotal) }}</span>
                        </div>
                        <a href="{{ route('checkout.index') }}" class="block w-full rounded-full bg-brand-600 px-4 py-3 text-center text-sm font-bold text-white hover:bg-brand-700">Continue</a>
                    </div>
                </aside>
            </div>
        </div>
    </template>
    @endif
</header>

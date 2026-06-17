@php
    $navLinks = [
        ['label' => 'Home', 'href' => route('home')],
        ['label' => 'Dresses', 'href' => route('categories.show', 'dresses')],
        ['label' => 'Tops', 'href' => route('categories.show', 'tops')],
        ['label' => 'Party Wear', 'href' => route('categories.show', 'party-wear')],
        ['label' => 'Sale', 'href' => route('deals')],
    ];
    $cartService = app(\App\Services\CartService::class);
    $productCatalog = app(\App\Services\ProductCatalog::class);
    $cartItems = collect($cartService->items())->map(function ($item) use ($productCatalog) {
        $product = $productCatalog->find($item['slug']);
        $item['available_sizes'] = $product['sizes'] ?? [];
        $item['size_hint'] = implode(', ', $item['available_sizes']);

        return $item;
    })->all();
    $cartSubtotal = $cartService->subtotal();
    $freeShippingAt = (float) config('currency.free_shipping_threshold', 2000);
    $freeShippingRemaining = max(0, $freeShippingAt - $cartSubtotal);
@endphp

<header class="sticky top-0 z-50 bg-surface-elevated/95 backdrop-blur-md border-b border-border" x-data="{ mobileOpen: false, searchOpen: false, cartOpen: {{ session('cart_drawer_open') ? 'true' : 'false' }} }">
    <div class="container-store">
        {{-- Main bar --}}
        <div class="flex items-center justify-between gap-4 py-4">
            {{-- Mobile menu toggle --}}
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

            {{-- Logo --}}
            <x-site.logo :show-name="true" name-class="hidden sm:block" />

            {{-- Desktop search --}}
            <div class="hidden lg:flex flex-1 max-w-xl mx-8">
                <form action="#" class="relative w-full">
                    <input
                        type="search"
                        placeholder="Search dresses, tops, styles..."
                        class="w-full rounded-full border border-border bg-surface py-2.5 pl-5 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-shadow"
                    >
                    <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-full bg-brand-600 text-white hover:bg-brand-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Actions --}}
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

                @auth
                    @if (! auth()->user()->isAdmin())
                        <a href="{{ route('account.dashboard') }}" class="p-2 text-ink-muted hover:text-ink transition-colors {{ request()->routeIs('account.*') ? 'text-brand-600' : '' }}" aria-label="My Account" title="{{ auth()->user()->name }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="p-2 text-ink-muted hover:text-ink transition-colors hidden sm:block" aria-label="Account">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </a>
                @endauth

                @auth
                    @if (! auth()->user()->isAdmin())
                        <button type="button" class="relative p-2 text-ink-muted hover:text-ink transition-colors" aria-label="Cart" @click="cartOpen = true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @if (($cartCount ?? 0) > 0)
                                <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center min-w-4 h-4 px-1 text-[10px] font-bold bg-brand-600 text-white rounded-full">{{ $cartCount }}</span>
                            @endif
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="relative p-2 text-ink-muted hover:text-ink transition-colors" aria-label="Cart">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </a>
                @endauth
            </div>
        </div>

        {{-- Mobile search --}}
        <div x-show="searchOpen" x-cloak class="lg:hidden pb-4">
            <form action="#" class="relative">
                <input
                    type="search"
                    placeholder="Search products..."
                    class="w-full rounded-full border border-border bg-surface py-2.5 pl-5 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                >
                <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-full bg-brand-600 text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Desktop nav --}}
        <nav class="hidden lg:flex items-center gap-8 pb-4 border-t border-border pt-4 -mt-px">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="text-sm font-medium text-ink-muted hover:text-brand-600 transition-colors {{ request()->routeIs('home') && $link['label'] === 'Home' ? 'text-brand-600' : '' }}"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>

    {{-- Mobile nav drawer --}}
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
                    class="px-3 py-2.5 rounded-lg text-sm font-medium text-ink-muted hover:bg-surface hover:text-brand-600 transition-colors"
                    @click="mobileOpen = false"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
            @guest
                <a href="{{ route('login') }}" class="px-3 py-2.5 rounded-lg text-sm font-medium text-ink-muted hover:bg-surface hover:text-brand-600 transition-colors" @click="mobileOpen = false">Sign In</a>
                <a href="{{ route('register') }}" class="px-3 py-2.5 rounded-lg text-sm font-semibold text-brand-600 hover:bg-brand-50 transition-colors" @click="mobileOpen = false">Register</a>
            @else
                @if (! auth()->user()->isAdmin())
                    <a href="{{ route('account.dashboard') }}" class="px-3 py-2.5 rounded-lg text-sm font-semibold text-brand-600 hover:bg-brand-50 transition-colors" @click="mobileOpen = false">My Account</a>
                @endif
            @endguest
        </nav>
    </div>

    {{-- Cart drawer --}}
    <div x-show="cartOpen" x-cloak class="fixed inset-0 z-10000" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/45" @click="cartOpen = false"></div>
        <aside class="fixed bottom-0 right-0 top-0 flex h-dvh min-h-dvh w-full max-w-sm flex-col bg-surface-elevated shadow-2xl" @click.stop>
            <div class="flex items-center justify-between border-b border-border px-4 py-3">
                <div>
                    <h2 class="text-base font-bold text-ink">Your Cart</h2>
                </div>
                <button type="button" class="rounded-full bg-surface p-1.5 text-ink-muted hover:text-ink" @click="cartOpen = false" aria-label="Close cart">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4 pb-2">
                @if (count($cartItems) > 0)
                    <div class="mb-3">
                        <p class="text-sm font-semibold text-ink">{{ $cartCount ?? 0 }} {{ Str::plural('Item', $cartCount ?? 0) }}</p>
                        @if ($freeShippingRemaining > 0)
                            <p class="mt-1 text-[11px] text-brand-600">Add {{ money($freeShippingRemaining) }} more to get free shipping.</p>
                        @endif
                    </div>

                    <div class="space-y-3">
                        @foreach ($cartItems as $item)
                            <div class="flex gap-3 border-b border-border pb-3 last:border-b-0" x-data="{ qty: {{ $item['quantity'] }} }">
                                <a href="{{ route('products.show', $item['slug']) }}" class="h-16 w-12 shrink-0 overflow-hidden rounded-lg border border-border bg-brand-50">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover object-top">
                                </a>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <a href="{{ route('products.show', $item['slug']) }}" class="line-clamp-2 text-xs font-semibold leading-snug text-ink hover:text-brand-600">{{ $item['name'] }}</a>
                                        <form action="{{ route('cart.remove', $item['key']) }}" method="POST" class="shrink-0">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="cart_drawer" value="1">
                                            <button type="submit" class="text-xs text-ink-muted hover:text-red-600" aria-label="Remove item">×</button>
                                        </form>
                                    </div>

                                    <form action="{{ route('cart.update', $item['key']) }}" method="POST" class="mt-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="cart_drawer" value="1">
                                        <input type="hidden" name="quantity" x-ref="qtyInput" :value="qty">

                                        <div class="flex items-end justify-between gap-2">
                                            <div>
                                                <label class="mb-1 block text-[10px] font-medium text-ink-muted">Size</label>
                                                <input
                                                    type="text"
                                                    name="size"
                                                    value="{{ $item['size'] }}"
                                                    placeholder="{{ $item['size_hint'] ?: 'Size' }}"
                                                    onchange="this.form.requestSubmit()"
                                                    class="h-8 w-20 rounded-md border border-border bg-surface px-2 text-[11px] text-ink placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500"
                                                >
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <p class="text-xs font-semibold text-ink">{{ money($item['price'] * $item['quantity']) }}</p>
                                                <div class="flex h-9 items-center overflow-hidden rounded-full border border-border bg-surface">
                                                    <button
                                                        type="button"
                                                        @click="if (qty > 1) { qty--; $refs.qtyInput.value = qty; $el.form.requestSubmit(); }"
                                                        class="flex h-full w-9 items-center justify-center text-sm text-ink-muted hover:bg-surface-elevated"
                                                    >-</button>
                                                    <span class="w-8 text-center text-sm font-semibold text-ink" x-text="qty"></span>
                                                    <button
                                                        type="button"
                                                        @click="if (qty < 10) { qty++; $refs.qtyInput.value = qty; $el.form.requestSubmit(); }"
                                                        class="flex h-full w-9 items-center justify-center text-sm bg-brand-600 text-white hover:bg-brand-700"
                                                    >+</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex h-full flex-col items-center justify-center text-center">
                        <div class="mb-4 rounded-full bg-brand-50 p-4 text-brand-600">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17"/></svg>
                        </div>
                        <p class="font-semibold text-ink">Your cart is empty</p>
                        <p class="mt-1 text-sm text-ink-muted">Add products to see them here.</p>
                    </div>
                @endif
            </div>

            <div class="shrink-0 border-t border-border bg-surface-elevated px-4 pb-[calc(4rem+env(safe-area-inset-bottom,0))] pt-3">
                <div>
                    <h3 class="mb-2 text-sm font-bold text-ink">Order Summary</h3>
                    <div class="mb-1.5 flex items-center justify-between text-xs">
                        <span class="text-ink-muted">Sub Total ({{ $cartCount ?? 0 }} {{ Str::plural('item', $cartCount ?? 0) }})</span>
                        <span class="font-medium text-ink">{{ money($cartSubtotal) }}</span>
                    </div>
                    <div class="mb-2 flex items-center justify-between border-b border-border pb-2 text-xs">
                        <span class="text-ink-muted">Discount</span>
                        <span class="font-medium text-ink">{{ money(0) }}</span>
                    </div>
                    <div class="mb-3 flex items-center justify-between text-sm">
                        <span class="font-bold text-ink">Total Amount</span>
                        <span class="font-bold text-ink">{{ money($cartSubtotal) }}</span>
                    </div>
                    <a href="{{ route('checkout.index') }}" class="block w-full rounded-full bg-brand-600 px-4 py-3 text-center text-sm font-bold text-white hover:bg-brand-700">Continue</a>
                </div>
            </div>
        </aside>
    </div>
</header>

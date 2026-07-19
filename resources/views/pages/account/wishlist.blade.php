@extends('layouts.account')

@section('title', 'Wishlist')
@section('page_title', 'Wishlist')
@section('mobile_title', 'Wishlist')
@section('page_subtitle', 'Products you saved for later')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Wishlist</span>
@endsection

@section('content')
    <div class="px-4 lg:px-8 pt-4 lg:pt-8 pb-6">
        @if (empty($items))
            <div class="account-panel">
                <div class="account-panel-body text-center py-14 sm:py-16">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-brand-50 text-brand-300 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-ink">Your wishlist is empty</p>
                    <p class="text-ink-muted mt-1">Tap the heart on any product to save it here.</p>
                    <a href="{{ route('shop.index') }}" class="inline-block mt-5 px-6 py-2.5 rounded-lg bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition-colors">Browse Shop</a>
                </div>
            </div>
        @else
            <div class="account-panel mb-5">
                <div class="account-panel-header">
                    <h2 class="font-semibold text-ink">Saved Products</h2>
                    <span class="text-sm text-ink-muted">{{ $count }} {{ Str::plural('item', $count) }}</span>
                </div>
                <div class="account-panel-body !p-3 sm:!p-4">
                    <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-7 xl:grid-cols-8 gap-2 sm:gap-2.5">
                        @foreach ($items as $product)
                            <article class="group relative flex flex-col rounded-lg border border-border bg-surface overflow-hidden hover:border-brand-200 transition-colors">
                                <div class="relative mx-auto mt-2 w-20 h-20 sm:w-24 sm:h-24 rounded-md bg-brand-50 overflow-hidden shrink-0">
                                    <a href="{{ $product['href'] }}" class="block w-full h-full">
                                        @if ($product['image'] ?? null)
                                            <img
                                                src="{{ $product['image'] }}"
                                                alt="{{ $product['name'] }}"
                                                class="w-full h-full object-cover object-top group-hover:scale-105 transition-transform duration-300"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-brand-50">
                                                <svg class="w-5 h-5 text-brand-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </a>

                                    <button
                                        type="button"
                                        class="absolute -top-1 -right-1 z-10 inline-flex items-center justify-center w-4 h-4 text-brand-600 drop-shadow-sm hover:text-brand-700 transition-colors"
                                        @click.stop.prevent="$store.wishlist.toggle(@js($product['slug'])); $el.closest('article').remove()"
                                        aria-label="Remove from wishlist"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="px-2 py-2 flex flex-col flex-1 gap-1 text-center">
                                    <a href="{{ $product['href'] }}" class="text-xs sm:text-sm font-medium text-ink hover:text-brand-600 line-clamp-2 leading-snug">
                                        {{ $product['name'] }}
                                    </a>
                                    <p class="mt-auto text-sm sm:text-base font-bold text-brand-700">{{ money($product['price']) }}</p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

<footer class="bg-ink text-white mt-auto">
    <div class="container-store py-12 lg:py-16">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
            {{-- Brand --}}
            <div class="sm:col-span-2 lg:col-span-1">
                <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
                    <span class="flex items-center justify-center w-9 h-9 rounded-lg bg-brand-600 text-white font-bold text-lg">L</span>
                    <span class="font-semibold text-xl">{{ config('app.name', 'Shop') }}</span>
                </a>
                <p class="text-zinc-400 text-sm leading-relaxed max-w-xs">
                    Premium women's fashion — dresses, tops, and party wear for every occasion.
                </p>
                <div class="flex gap-3 mt-6">
                    <a href="https://facebook.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 hover:bg-brand-600 hover:text-white transition-colors" aria-label="Facebook">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </a>
                    <a href="https://instagram.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 hover:bg-brand-600 hover:text-white transition-colors" aria-label="Instagram">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                        </svg>
                    </a>
                    <a href="https://tiktok.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 hover:bg-brand-600 hover:text-white transition-colors" aria-label="TikTok">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V8.5a8.28 8.28 0 004.77 1.52V6.5a4.85 4.85 0 01-1.01-.19z"/>
                        </svg>
                    </a>
                    <a href="https://youtube.com" target="_blank" rel="noopener noreferrer" class="w-9 h-9 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 hover:bg-brand-600 hover:text-white transition-colors" aria-label="YouTube">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Shop --}}
            <div>
                <h3 class="font-semibold text-sm uppercase tracking-wider mb-4">Shop</h3>
                <ul class="space-y-2.5 text-sm text-zinc-400">
                    <li><a href="{{ route('categories.show', 'dresses') }}" class="hover:text-white transition-colors">Dresses</a></li>
                    <li><a href="{{ route('categories.show', 'tops') }}" class="hover:text-white transition-colors">Tops & Blouses</a></li>
                    <li><a href="{{ route('categories.show', 'party-wear') }}" class="hover:text-white transition-colors">Party Wear</a></li>
                    <li><a href="{{ route('deals') }}" class="hover:text-white transition-colors">Sale</a></li>
                </ul>
            </div>

            {{-- Support --}}
            <div>
                <h3 class="font-semibold text-sm uppercase tracking-wider mb-4">Support</h3>
                <ul class="space-y-2.5 text-sm text-zinc-400">
                    <li><a href="#" class="hover:text-white transition-colors">Contact Us</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">FAQs</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Shipping Info</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Returns</a></li>
                </ul>
            </div>

            {{-- Newsletter --}}
            <div>
                <h3 class="font-semibold text-sm uppercase tracking-wider mb-4">Stay Updated</h3>
                <p class="text-sm text-zinc-400 mb-4">Get exclusive deals and new arrivals in your inbox.</p>
                <form action="#" class="flex gap-2">
                    <input
                        type="email"
                        placeholder="Your email"
                        class="flex-1 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-brand-500/50"
                    >
                    <button type="submit" class="shrink-0 px-4 py-2 rounded-lg bg-brand-600 text-sm font-medium hover:bg-brand-500 transition-colors">
                        Join
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-zinc-500">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Shop') }}. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-white transition-colors">Privacy</a>
                <a href="#" class="hover:text-white transition-colors">Terms</a>
                <a href="#" class="hover:text-white transition-colors">Cookies</a>
            </div>
        </div>
    </div>
</footer>

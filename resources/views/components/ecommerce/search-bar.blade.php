@props([
    'query' => '',
    'inputClass' => 'w-full rounded-full border border-border bg-surface py-2.5 pl-5 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 transition-shadow',
    'placeholder' => 'Search dresses, tops, styles...',
])

@php
    $searchUrl = route('search.index');
    $suggestUrl = route('search.suggest');
@endphp

<div
    {{ $attributes->merge(['class' => 'relative w-full']) }}
    x-data="{
        query: @js($query),
        results: [],
        loading: false,
        open: false,
        suggestUrl: @js($suggestUrl),
        searchUrl: @js($searchUrl),
        debounceTimer: null,
        async fetchSuggestions() {
            const term = this.query.trim();

            if (term.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(`${this.suggestUrl}?q=${encodeURIComponent(term)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (! response.ok) {
                    throw new Error('Search failed');
                }

                const data = await response.json();
                this.results = data.results || [];
                this.open = true;
            } catch (error) {
                this.results = [];
                this.open = false;
            } finally {
                this.loading = false;
            }
        },
        onInput() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.fetchSuggestions(), 300);
        },
        submitSearch() {
            const term = this.query.trim();

            if (term === '') {
                return;
            }

            window.location.href = `${this.searchUrl}?q=${encodeURIComponent(term)}`;
        },
        closeDropdown() {
            this.open = false;
        },
    }"
    @click.outside="closeDropdown()"
>
    <form @submit.prevent="submitSearch()" class="relative w-full">
        <input
            type="search"
            name="q"
            x-model="query"
            @input="onInput()"
            @focus="if (query.trim().length >= 2) { fetchSuggestions(); }"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="{{ $inputClass }}"
        >
        <button
            type="submit"
            class="absolute right-1.5 top-1/2 -translate-y-1/2 p-2 rounded-full bg-brand-600 text-white hover:bg-brand-700 transition-colors"
            aria-label="Search"
        >
            <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <svg x-show="loading" x-cloak class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </form>

    <div
        x-show="open"
        x-cloak
        class="absolute left-0 right-0 top-full z-[60] mt-2 overflow-hidden rounded-2xl border border-border bg-surface-elevated shadow-xl"
    >
        <template x-if="results.length > 0">
            <div class="max-h-80 overflow-y-auto py-2">
                <template x-for="item in results" :key="item.slug">
                    <a
                        :href="item.href"
                        class="flex items-center gap-3 px-4 py-3 hover:bg-brand-50 transition-colors"
                        @click="closeDropdown()"
                    >
                        <div class="h-12 w-10 shrink-0 overflow-hidden rounded-lg border border-border bg-brand-50">
                            <img
                                :src="item.image"
                                :alt="item.name"
                                class="h-full w-full object-cover object-top"
                                loading="lazy"
                                x-show="item.image"
                            >
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink" x-text="item.name"></p>
                            <p class="text-xs font-semibold text-brand-700" x-text="item.price_formatted || ''"></p>
                        </div>
                    </a>
                </template>
                <button
                    type="button"
                    class="w-full border-t border-border px-4 py-3 text-left text-sm font-semibold text-brand-600 hover:bg-brand-50"
                    @click="submitSearch()"
                >
                    View all results
                </button>
            </div>
        </template>

        <template x-if="results.length === 0 && query.trim().length >= 2 && !loading">
            <div class="px-4 py-5 text-sm text-ink-muted">
                No products found for “<span x-text="query.trim()"></span>”.
            </div>
        </template>
    </div>
</div>

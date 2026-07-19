/**
 * Storefront wishlist (Alpine.store).
 */
export function registerWishlistStore(Alpine) {
    const boot = window.__WISHLIST_BOOT__ || {};

    Alpine.store('wishlist', {
        count: Number(boot.count || 0),
        slugs: Array.isArray(boot.slugs) ? boot.slugs.map(String) : [],
        toggleUrl: boot.toggleUrl || '/wishlist/toggle',
        loginUrl: boot.loginUrl || '/login',
        csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',

        has(slug) {
            return this.slugs.includes(String(slug));
        },

        async toggle(slug) {
            if (! slug) return;

            try {
                const response = await fetch(this.toggleUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ slug }),
                });

                if (response.status === 401 || response.status === 403) {
                    const data = await response.json().catch(() => ({}));
                    window.location.href = data.redirect || this.loginUrl;
                    return;
                }

                if (! response.ok) {
                    return;
                }

                const data = await response.json();
                this.count = Number(data.count || 0);
                this.slugs = Array.isArray(data.slugs) ? data.slugs.map(String) : [];
            } catch (e) {
                // ignore network errors for toggle UX
            }
        },
    });
}

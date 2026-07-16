/**
 * Shared storefront cart drawer + add-to-cart (Alpine.store).
 * Avoids fragile inline x-data / x-teleport scope issues.
 */
export function registerCartStore(Alpine) {
    const boot = window.__CART_BOOT__ || {};

    Alpine.store('cart', {
        open: !!boot.open,
        enabled: boot.enabled !== false,
        items: Array.isArray(boot.items) ? boot.items : [],
        count: Number(boot.count || 0),
        subtotal: boot.subtotal || '',
        discountAmount: Number(boot.discountAmount || 0),
        discount: boot.discount || '',
        shipping: boot.shipping || '',
        total: boot.total || '',
        freeShippingRemaining: Number(boot.freeShippingRemaining || 0),
        freeShippingRemainingFormatted: boot.freeShippingRemainingFormatted || '',

        apply(cart) {
            if (!cart) return;
            this.items = cart.items || [];
            this.count = cart.cart_count ?? 0;
            this.subtotal = cart.subtotal_formatted || '';
            this.discountAmount = cart.discount ?? 0;
            this.discount = cart.discount_formatted || '';
            this.shipping = cart.shipping_formatted || '';
            this.total = cart.total_formatted || '';
            this.freeShippingRemaining = cart.free_shipping_remaining ?? 0;
            this.freeShippingRemainingFormatted = cart.free_shipping_remaining_formatted || '';
        },

        openDrawer() {
            if (this.enabled) this.open = true;
        },

        closeDrawer() {
            this.open = false;
        },

        async addFromForm(form) {
            if (!form || !form.action) return;

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                const cart = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (cart.redirect) {
                        window.location.href = cart.redirect;
                        return;
                    }
                    throw new Error(cart.message || 'Cart add failed.');
                }

                this.apply(cart);

                if (window.BahariTracking) {
                    const product =
                        window.BahariTracking.productFromDataset(form) ||
                        window.BahariTracking.productFromDataset(form.closest('[data-track-product]'));
                    if (product) {
                        const qtyInput = form.querySelector('[name="quantity"]');
                        if (qtyInput) {
                            product.quantity = parseInt(qtyInput.value || '1', 10) || 1;
                        }
                        window.BahariTracking.addToCart(product, cart.tracking_event_id || undefined);
                    }
                }

                this.openDrawer();
            } catch (error) {
                // Fallback to normal form submit (login redirect / validation).
                if (typeof form.submit === 'function') {
                    HTMLFormElement.prototype.submit.call(form);
                }
            }
        },

        async updateItem(form, item, nextQty) {
            if (!form || !item) return;

            const formData = new FormData(form);
            formData.set('quantity', String(nextQty));
            formData.set('cart_drawer', '1');

            item.quantity = nextQty;
            item.syncing = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Cart update failed.');

                const cart = await response.json();
                this.apply(cart);
            } catch (error) {
                if (form.requestSubmit) form.requestSubmit();
            } finally {
                item.syncing = false;
            }
        },

        async removeItem(form, item) {
            if (!form || !item) return;

            const formData = new FormData(form);
            formData.set('cart_drawer', '1');
            item.syncing = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Cart remove failed.');

                const cart = await response.json();
                if (window.BahariTracking) {
                    window.BahariTracking.removeFromCart(
                        {
                            product_id: item.slug,
                            product_sku: item.slug,
                            product_name: item.name,
                            product_price: item.price,
                            quantity: item.quantity,
                            variant_id: [item.size, item.color].filter(Boolean).join(' / ') || item.slug,
                        },
                        cart.tracking_event_id
                    );
                }
                this.apply(cart);
            } catch (error) {
                if (form.requestSubmit) form.requestSubmit();
            } finally {
                item.syncing = false;
            }
        },
    });

    // Global listener so product cards work even if header scope is broken.
    window.addEventListener('cart:add', (event) => {
        const form = event?.detail?.form;
        if (form) {
            Alpine.store('cart').addFromForm(form);
        }
    });
}

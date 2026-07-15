/**
 * Push flat ecommerce events into dataLayer for the existing GTM container.
 * Browser Pixel / GA4 tags live in GTM — this only feeds events + event_id.
 */
(function () {
    const currency = () =>
        (window.__TRACKING__ && window.__TRACKING__.currency) || 'BDT';

    function uuid() {
        if (window.crypto && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    function push(eventName, payload = {}, eventId) {
        window.dataLayer = window.dataLayer || [];
        const id = eventId || payload.event_id || uuid();
        const data = Object.assign({}, payload, {
            event: eventName,
            event_id: id,
            eventID: id, // Meta Pixel eventID for CAPI dedupe
            currency: payload.currency || currency(),
            presentment_currency: payload.presentment_currency || payload.currency || currency(),
            page_currency: payload.page_currency || payload.currency || currency(),
            meta_currency: payload.meta_currency || payload.currency || currency(),
        });

        if (eventName === 'meta_track' || payload.meta_event_name) {
            data.meta_event_id = data.meta_event_id || id;
            data.meta_event_name = data.meta_event_name || payload.meta_event_name;
        }

        window.dataLayer.push(data);
        return id;
    }

    function productFromDataset(el) {
        if (!el || !el.dataset) return null;
        return {
            product_id: el.dataset.productId || el.dataset.slug || '',
            product_sku: el.dataset.productSku || el.dataset.slug || '',
            google_product_id: el.dataset.productSku || el.dataset.slug || '',
            product_name: el.dataset.productName || '',
            product_price: parseFloat(el.dataset.productPrice || '0') || 0,
            product_type: el.dataset.productType || '',
            product_brand: el.dataset.productBrand || '',
            quantity: parseInt(el.dataset.quantity || '1', 10) || 1,
            variant_id: el.dataset.variantId || el.dataset.slug || '',
            product_position: el.dataset.productPosition
                ? parseInt(el.dataset.productPosition, 10)
                : undefined,
            collection_name: el.dataset.listName || undefined,
        };
    }

    function enrichProduct(p) {
        if (!p) return {};
        const qty = p.quantity || 1;
        const price = Number(p.product_price || p.price || 0);
        const id = p.product_id || p.slug || '';
        return Object.assign({}, p, {
            product_id: id,
            product_sku: p.product_sku || id,
            google_product_id: p.google_product_id || p.product_sku || id,
            price: price,
            total_value: Math.round(price * qty * 100) / 100,
            meta_value: Math.round(price * qty * 100) / 100,
            meta_content_ids: [id],
            meta_content_type: 'product',
            meta_content_name: p.product_name || '',
            meta_contents: [{ id: id, quantity: qty, item_price: price }],
            ecomm_prodid: id,
            currency: currency(),
            presentment_currency: currency(),
            page_currency: currency(),
            meta_currency: currency(),
        });
    }

    const BahariTracking = {
        push,
        uuid,
        viewItem(product, eventId) {
            return push('view_item', enrichProduct(product), eventId);
        },
        addToCart(product, eventId) {
            return push('add_to_cart', enrichProduct(product), eventId);
        },
        removeFromCart(product, eventId) {
            return push('ee_removeFromCart', enrichProduct(product), eventId);
        },
        viewCart(payload, eventId) {
            return push('ee_cartView', payload || {}, eventId);
        },
        beginCheckout(payload, eventId) {
            return push('begin_checkout', payload || {}, eventId);
        },
        addShippingInfo(payload, eventId) {
            return push('add_shipping_info', payload || {}, eventId);
        },
        addPaymentInfo(payload, eventId) {
            return push('add_payment_info', payload || {}, eventId);
        },
        purchase(payload, eventId) {
            const id = push('purchase', payload || {}, eventId);
            push(
                'meta_track',
                Object.assign({}, payload || {}, {
                    meta_event_name: 'Purchase',
                    meta_event_id: id,
                }),
                id
            );
            return id;
        },
        login(payload, eventId) {
            return push(
                'login',
                Object.assign({ method: 'email' }, payload || {}),
                eventId
            );
        },
        signUp(payload, eventId) {
            const id = push(
                'sign_up',
                Object.assign({ method: 'email' }, payload || {}),
                eventId
            );
            push(
                'meta_track',
                Object.assign({}, payload || {}, {
                    meta_event_name: 'CompleteRegistration',
                    meta_event_id: id,
                }),
                id
            );
            return id;
        },
        search(term, resultsCount, eventId) {
            return push(
                'view_search_results',
                {
                    search_term: term || '',
                    search_results: resultsCount || 0,
                },
                eventId
            );
        },
        productImpression(products, listName) {
            if (!products || !products.length) return;
            const names = [];
            const prices = [];
            const types = [];
            const brands = [];
            const ids = [];
            const positions = [];
            products.forEach((p, i) => {
                names.push(p.product_name || p.name || '');
                prices.push(Number(p.product_price || p.price || 0));
                types.push(p.product_type || p.category || '');
                brands.push(p.product_brand || p.brand || '');
                ids.push(p.product_id || p.slug || '');
                positions.push(p.product_position || i + 1);
            });
            push('ee_productImpression', {
                product_name: names,
                product_price: prices,
                product_type: types,
                product_brand: brands,
                product_id: ids,
                product_sku: ids,
                google_product_id: ids,
                product_position: positions,
                collection_name: listName || 'Product List',
            });
        },
        productClick(product) {
            return push('ee_productClick', enrichProduct(product));
        },
        pageInfo(extra) {
            return push(
                'sh_info',
                Object.assign(
                    {
                        page_type: (window.__TRACKING__ && window.__TRACKING__.page_type) || 'page',
                    },
                    extra || {}
                )
            );
        },
        productFromDataset,
        enrichProduct,
    };

    window.BahariTracking = BahariTracking;

    document.addEventListener('DOMContentLoaded', () => {
        // Page boot events are pushed inline in tracking-boot (before GTM).
        // Only wire interactive handlers here.
        document.querySelectorAll('[data-track-product]').forEach((el) => {
            el.addEventListener('click', () => {
                const p = BahariTracking.productFromDataset(el);
                if (p) BahariTracking.productClick(p);
            });
        });
    });
})();

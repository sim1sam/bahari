@props([
    'pageType' => 'page',
    'eventId' => null,
    'viewItem' => null,
    'viewCart' => null,
    'beginCheckout' => null,
    'purchase' => null,
    'search' => null,
    'impressions' => null,
    'listName' => null,
    'user' => null,
    'login' => null,
    'signUp' => null,
])

@php
    $authFlash = session('tracking_auth');
    if (is_array($authFlash)) {
        if (($authFlash['event'] ?? null) === 'login' && $login === null) {
            $login = array_merge(
                ['method' => $authFlash['method'] ?? 'email'],
                $authFlash['user'] ?? [],
                ['event_id' => $authFlash['event_id'] ?? null]
            );
            $eventId = $eventId ?: ($authFlash['event_id'] ?? null);
        }
        if (($authFlash['event'] ?? null) === 'sign_up' && $signUp === null) {
            $signUp = array_merge(
                ['method' => $authFlash['method'] ?? 'email'],
                $authFlash['user'] ?? [],
                ['event_id' => $authFlash['event_id'] ?? null]
            );
            $eventId = $eventId ?: ($authFlash['event_id'] ?? null);
        }
        if ($user === null && ! empty($authFlash['user'])) {
            $user = $authFlash['user'];
        }
    }

    $payload = array_filter([
        'page_type' => $pageType,
        'currency' => \App\Support\TrackingPayload::currency(),
        'event_id' => $eventId,
        'view_item' => $viewItem,
        'view_cart' => $viewCart,
        'begin_checkout' => $beginCheckout,
        'purchase' => $purchase,
        'search' => $search,
        'impressions' => $impressions,
        'list_name' => $listName,
        'user' => $user,
        'login' => $login,
        'sign_up' => $signUp,
    ], fn ($v) => $v !== null && $v !== []);
@endphp

{{-- Must run before GTM so Tag Assistant / GTM see ecommerce events immediately --}}
<script>
    window.dataLayer = window.dataLayer || [];
    window.__TRACKING__ = @json($payload);
    window.__TRACKING_BOOTED__ = false;
    (function (boot) {
        if (!boot || window.__TRACKING_BOOTED__) return;
        window.__TRACKING_BOOTED__ = true;

        function push(eventName, data, eventId) {
            var payload = Object.assign({}, data || {}, {
                event: eventName,
                event_id: eventId || (data && data.event_id) || (boot.event_id) || undefined,
                currency: (data && data.currency) || boot.currency || 'BDT',
                presentment_currency: (data && data.presentment_currency) || boot.currency || 'BDT',
                page_currency: (data && data.page_currency) || boot.currency || 'BDT',
                meta_currency: (data && data.meta_currency) || boot.currency || 'BDT'
            });
            window.dataLayer.push(payload);
            return payload.event_id;
        }

        if (boot.user) {
            window.dataLayer.push(Object.assign({}, boot.user));
        }

        push('sh_info', { page_type: boot.page_type || 'page' }, boot.event_id);

        if (boot.view_item) {
            push('view_item', boot.view_item, boot.event_id);
        }
        if (boot.view_cart) {
            push('ee_cartView', boot.view_cart, boot.event_id);
        }
        if (boot.begin_checkout) {
            push('begin_checkout', boot.begin_checkout, boot.event_id);
        }
        if (boot.purchase) {
            var purchaseId = push('purchase', boot.purchase, boot.event_id);
            push('meta_track', Object.assign({}, boot.purchase, {
                meta_event_name: 'Purchase',
                meta_event_id: purchaseId
            }), purchaseId);
        }
        if (boot.login) {
            push('login', Object.assign({ method: 'email' }, boot.login), boot.login.event_id || boot.event_id);
        }
        if (boot.sign_up) {
            var signUpId = push('sign_up', Object.assign({ method: 'email' }, boot.sign_up), boot.sign_up.event_id || boot.event_id);
            push('meta_track', Object.assign({}, boot.sign_up, {
                meta_event_name: 'CompleteRegistration',
                meta_event_id: signUpId
            }), signUpId);
        }
        if (boot.search) {
            push('view_search_results', {
                search_term: boot.search.term || '',
                search_results: boot.search.results || 0
            }, boot.event_id);
        }
        if (boot.impressions && boot.impressions.length) {
            var names = [], prices = [], types = [], brands = [], ids = [], positions = [];
            boot.impressions.forEach(function (p, i) {
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
                collection_name: boot.list_name || 'Product List'
            });
        }
    })(window.__TRACKING__);
</script>

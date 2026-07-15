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

<script>
    window.__TRACKING__ = @json($payload);
</script>

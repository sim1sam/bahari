<?php

namespace App\Http\Controllers;

use App\Services\MetaConversionsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetaCapiDebugController extends Controller
{
    public function __invoke(Request $request, MetaConversionsApiService $capi): JsonResponse
    {
        $key = trim((string) $request->query('key', ''));
        $allowed = array_values(array_filter([
            $this->raw('META_CAPI_TEST_EVENT_CODE') ?: config('services.meta.test_event_code'),
            $this->raw('META_PIXEL_ID') ?: config('services.meta.pixel_id'),
            'bahari-meta-capi',
        ]));

        $authorized = $key !== '' && collect($allowed)->contains(
            fn ($allowedKey) => hash_equals((string) $allowedKey, $key)
        );

        if (! $authorized) {
            return response()->json([
                'ok' => false,
                'error' => 'unauthorized',
                'message' => 'Pass ?key=YOUR_PIXEL_ID (or bahari-meta-capi).',
            ], 401);
        }

        $diag = $capi->diagnostics();
        $eventName = (string) $request->query('event', 'ViewContent');
        $allowedEvents = ['PageView', 'ViewContent', 'AddToCart', 'InitiateCheckout', 'Purchase', 'CompleteRegistration'];
        if (! in_array($eventName, $allowedEvents, true)) {
            $eventName = 'ViewContent';
        }

        $eventId = $capi->newEventId();
        $custom = match ($eventName) {
            'ViewContent', 'AddToCart' => [
                'content_ids' => ['debug-product'],
                'content_type' => 'product',
                'content_name' => 'Debug Product View',
                'currency' => config('currency.code', 'BDT'),
                'value' => 1,
            ],
            default => ['content_name' => 'meta-capi-debug'],
        };

        $sent = $capi->send(
            $eventName,
            $eventId,
            $custom,
            $capi->userDataFromCustomer([]),
            url('/'),
        );

        return response()->json([
            'ok' => $sent,
            'diagnostics' => $diag,
            'test_send' => [
                'event' => $eventName,
                'event_id' => $eventId,
                'hint' => $sent
                    ? "Success. Meta Events Manager → Overview should show {$eventName} (Server)."
                    : 'Send failed. Check storage/logs/laravel.log.',
            ],
        ]);
    }

    private function raw(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}

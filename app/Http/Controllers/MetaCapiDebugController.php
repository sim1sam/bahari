<?php

namespace App\Http\Controllers;

use App\Services\MetaConversionsApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MetaCapiDebugController extends Controller
{
    public function __invoke(Request $request, MetaConversionsApiService $capi): JsonResponse
    {
        $expected = trim((string) (
            config('services.meta.test_event_code')
            ?: ($_ENV['META_CAPI_TEST_EVENT_CODE'] ?? $_SERVER['META_CAPI_TEST_EVENT_CODE'] ?? getenv('META_CAPI_TEST_EVENT_CODE') ?: '')
        ));

        $key = (string) $request->query('key', '');
        if ($expected === '' || ! hash_equals($expected, $key)) {
            abort(404);
        }

        $diag = $capi->diagnostics();
        $eventId = $capi->newEventId();
        $sent = $capi->send(
            'PageView',
            $eventId,
            ['content_name' => 'meta-capi-debug'],
            $capi->userDataFromCustomer([]),
            url('/'),
        );

        return response()->json([
            'diagnostics' => $diag,
            'test_send' => [
                'ok' => $sent,
                'event' => 'PageView',
                'event_id' => $eventId,
                'hint' => $sent
                    ? 'Open Meta Events Manager → Test events → enter TEST89492. You should see PageView.'
                    : 'Send failed. Check storage/logs/laravel.log for Meta CAPI entries.',
            ],
        ]);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MetaConversionsApiService
{
    public function __construct(
        private SiteSettingsService $settings,
    ) {}

    /**
     * @param  array<string, mixed>  $customData
     * @param  array<string, mixed>  $userData
     */
    public function send(
        string $eventName,
        string $eventId,
        array $customData = [],
        array $userData = [],
        ?string $eventSourceUrl = null,
        ?string $actionSource = 'website',
    ): bool {
        try {
            $pixelId = $this->settings->metaPixelId();
            $token = $this->settings->metaCapiAccessToken();

            if (! $pixelId || ! $token) {
                Log::warning('Meta CAPI skipped: missing pixel or token', [
                    'event' => $eventName,
                    'pixel' => $pixelId,
                    'has_token' => filled($token),
                    'enabled' => $this->settings->metaCapiEnabled(),
                ]);

                return false;
            }

            if (! $this->settings->metaCapiEnabled()) {
                Log::warning('Meta CAPI skipped: disabled', ['event' => $eventName]);

                return false;
            }

            $userData = $this->normalizeUserData($userData);
            if (empty($userData['client_ip_address'])) {
                $userData['client_ip_address'] = request()->ip() ?: '0.0.0.0';
            }
            if (empty($userData['client_user_agent'])) {
                $userData['client_user_agent'] = request()->userAgent() ?: 'Laravel-Meta-CAPI';
            }

            $payload = [
                'data' => [[
                    'event_name' => $eventName,
                    'event_time' => time(),
                    'event_id' => $eventId,
                    'event_source_url' => $eventSourceUrl
                        ?: (string) (request()->headers->get('referer') ?: url()->current() ?: config('app.url')),
                    'action_source' => $actionSource ?: 'website',
                    'user_data' => $userData,
                    'custom_data' => $this->normalizeCustomData($customData),
                ]],
                'access_token' => $token,
            ];

            $testCode = $this->settings->metaCapiTestEventCode();
            if ($testCode) {
                $payload['test_event_code'] = $testCode;
            }

            $version = config('services.meta.api_version', 'v21.0');
            $url = "https://graph.facebook.com/{$version}/{$pixelId}/events";

            $result = $this->postJson($url, $payload);

            if (! ($result['ok'] ?? false)) {
                Log::warning('Meta CAPI request failed', [
                    'event' => $eventName,
                    'event_id' => $eventId,
                    'status' => $result['status'] ?? null,
                    'body' => $result['body'] ?? null,
                    'error' => $result['error'] ?? null,
                ]);

                return false;
            }

            Log::info('Meta CAPI sent', [
                'event' => $eventName,
                'event_id' => $eventId,
                'events_received' => data_get($result['json'], 'events_received'),
                'fbtrace_id' => data_get($result['json'], 'fbtrace_id'),
                'test_event_code' => $testCode,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::warning('Meta CAPI exception: '.$e->getMessage(), [
                'event' => $eventName,
                'event_id' => $eventId,
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status?: int, body?: string, json?: mixed, error?: string}
     */
    private function postJson(string $url, array $payload): array
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return ['ok' => false, 'error' => 'json_encode failed'];
        }

        // Prefer cURL — more reliable on shared hosting than Guzzle TLS quirks.
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            $body = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($body === false) {
                return ['ok' => false, 'status' => $status, 'error' => $curlError ?: 'curl_exec failed'];
            }

            $decoded = json_decode($body, true);

            return [
                'ok' => $status >= 200 && $status < 300,
                'status' => $status,
                'body' => $body,
                'json' => $decoded,
                'error' => $curlError ?: null,
            ];
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
                'content' => $json,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $status = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $status = (int) $m[1];
        }

        if ($body === false) {
            return ['ok' => false, 'status' => $status, 'error' => 'file_get_contents failed'];
        }

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'body' => $body,
            'json' => json_decode($body, true),
        ];
    }

    public function newEventId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * @return array<string, mixed>
     */
    public function diagnostics(): array
    {
        $pixel = $this->settings->metaPixelId();
        $token = $this->settings->metaCapiAccessToken();
        $test = $this->settings->metaCapiTestEventCode();

        return [
            'enabled' => $this->settings->metaCapiEnabled(),
            'pixel_id' => $pixel,
            'has_token' => filled($token),
            'token_length' => $token ? strlen($token) : 0,
            'test_event_code' => $test,
            'api_version' => config('services.meta.api_version', 'v21.0'),
            'curl' => function_exists('curl_init'),
            'config_pixel' => config('services.meta.pixel_id'),
            'env_pixel' => $this->rawEnv('META_PIXEL_ID'),
            'env_has_token' => filled($this->rawEnv('META_CAPI_ACCESS_TOKEN')),
        ];
    }

    private function rawEnv(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * @param  array<string, mixed>  $userData
     * @return array<string, mixed>
     */
    private function normalizeUserData(array $userData): array
    {
        $out = [];

        foreach (['em', 'ph', 'fn', 'ln', 'ct', 'st', 'zp', 'country', 'external_id'] as $key) {
            if (! empty($userData[$key])) {
                $out[$key] = is_array($userData[$key])
                    ? array_values(array_filter($userData[$key]))
                    : [$userData[$key]];
            }
        }

        if (! empty($userData['client_ip_address'])) {
            $out['client_ip_address'] = $userData['client_ip_address'];
        }
        if (! empty($userData['client_user_agent'])) {
            $out['client_user_agent'] = $userData['client_user_agent'];
        }
        if (! empty($userData['fbp'])) {
            $out['fbp'] = $userData['fbp'];
        }
        if (! empty($userData['fbc'])) {
            $out['fbc'] = $userData['fbc'];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $customData
     * @return array<string, mixed>
     */
    private function normalizeCustomData(array $customData): array
    {
        $out = array_filter($customData, fn ($v) => $v !== null && $v !== '');

        if (isset($out['value'])) {
            $out['value'] = round((float) $out['value'], 2);
        }
        if (isset($out['currency'])) {
            $out['currency'] = strtoupper((string) $out['currency']);
        }

        return $out;
    }

    public static function hash(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/\s+/', '', $normalized) ?: '';

        if ($normalized === '') {
            return null;
        }

        return hash('sha256', $normalized);
    }

    public static function hashPhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        return $digits !== '' ? hash('sha256', $digits) : null;
    }

    /**
     * @param  array<string, mixed>  $customer
     * @return array<string, mixed>
     */
    public function userDataFromCustomer(array $customer, ?int $externalId = null): array
    {
        $name = trim((string) ($customer['name'] ?? ''));
        $parts = preg_split('/\s+/', $name, 2) ?: [];

        $data = [
            'em' => self::hash($customer['email'] ?? null),
            'ph' => self::hashPhone($customer['phone'] ?? null),
            'fn' => self::hash($parts[0] ?? null),
            'ln' => self::hash($parts[1] ?? null),
            'ct' => self::hash($customer['city'] ?? null),
            'zp' => self::hash($customer['zip'] ?? null),
            'country' => self::hash('bd'),
            'client_ip_address' => request()->ip(),
            'client_user_agent' => request()->userAgent(),
            'fbp' => request()->cookie('_fbp'),
            'fbc' => request()->cookie('_fbc'),
        ];

        if ($externalId) {
            $data['external_id'] = self::hash((string) $externalId);
        }

        return array_filter($data, fn ($v) => $v !== null && $v !== '');
    }
}

<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
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
        if (! $this->settings->metaCapiEnabled()) {
            return false;
        }

        $pixelId = $this->settings->metaPixelId();
        $token = $this->settings->metaCapiAccessToken();

        if (! $pixelId || ! $token) {
            return false;
        }

        $payload = [
            'data' => [[
                'event_name' => $eventName,
                'event_time' => time(),
                'event_id' => $eventId,
                'event_source_url' => $eventSourceUrl ?: url()->previous() ?: config('app.url'),
                'action_source' => $actionSource,
                'user_data' => $this->normalizeUserData($userData),
                'custom_data' => $this->normalizeCustomData($customData),
            ]],
        ];

        $testCode = $this->settings->metaCapiTestEventCode();
        if ($testCode) {
            $payload['test_event_code'] = $testCode;
        }

        $version = config('services.meta.api_version', 'v21.0');
        $url = "https://graph.facebook.com/{$version}/{$pixelId}/events";

        try {
            $response = Http::asJson()
                ->timeout(8)
                ->post($url.'?access_token='.urlencode($token), $payload);

            if (! $response->successful()) {
                Log::warning('Meta CAPI request failed', [
                    'event' => $eventName,
                    'event_id' => $eventId,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (ConnectionException|Throwable $e) {
            Log::warning('Meta CAPI exception: '.$e->getMessage(), [
                'event' => $eventName,
                'event_id' => $eventId,
            ]);

            return false;
        }
    }

    public function newEventId(): string
    {
        return (string) Str::uuid();
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

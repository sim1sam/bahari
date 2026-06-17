<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTransferSetting extends Model
{
    protected $fillable = [
        'site_name',
        'domain',
        'endpoint_path',
        'api_key',
        'access_token',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'endpoint_path' => '/api/orders/import',
            'is_active' => false,
        ]);
    }

    public function endpointUrl(): ?string
    {
        if (! $this->domain) {
            return null;
        }

        $domain = rtrim($this->domain, '/');
        $path = '/'.ltrim($this->endpoint_path ?: '/api/orders/import', '/');

        return $domain.$path;
    }

    public function isConfigured(): bool
    {
        return $this->is_active
            && filled($this->domain)
            && filled($this->api_key)
            && filled($this->access_token);
    }
}

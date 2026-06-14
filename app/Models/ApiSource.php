<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiSource extends Model
{
    protected $fillable = ['name', 'api_key', 'api_token', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function receivedItems(): HasMany
    {
        return $this->hasMany(ApiReceivedItem::class);
    }

    public static function generateCredentials(): array
    {
        return [
            'api_key' => 'ak_'.Str::random(32),
            'api_token' => 'at_'.Str::random(48),
        ];
    }

    public function matchesToken(string $token): bool
    {
        return hash_equals($this->api_token, $token);
    }
}

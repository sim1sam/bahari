<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const SLUG_ADMIN = 'admin';

    public const SLUG_CUSTOMER = 'customer';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'can_access_admin',
        'is_active',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'can_access_admin' => 'boolean',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function hasFeature(string $key): bool
    {
        if (! $this->can_access_admin || ! $this->is_active) {
            return false;
        }

        return in_array($key, $this->permissions ?? [], true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSystem(): bool
    {
        return in_array($this->slug, [self::SLUG_ADMIN, self::SLUG_CUSTOMER], true);
    }
}

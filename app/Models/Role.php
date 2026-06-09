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
    ];

    protected function casts(): array
    {
        return [
            'can_access_admin' => 'boolean',
        ];
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

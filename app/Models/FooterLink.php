<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FooterLink extends Model
{
    public const GROUP_SHOP = 'shop';

    public const GROUP_SUPPORT = 'support';

    public const GROUP_LEGAL = 'legal';

    public const GROUPS = [
        self::GROUP_SHOP => 'Shop',
        self::GROUP_SUPPORT => 'Support',
        self::GROUP_LEGAL => 'Legal',
    ];

    protected $fillable = ['group', 'label', 'url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

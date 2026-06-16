<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerAddress extends Model
{
    public const TYPE_HOME = 'home';
    public const TYPE_OFFICE = 'office';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'type',
        'label',
        'recipient_name',
        'phone',
        'address_line',
        'city',
        'zip',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_OFFICE => 'Office',
            self::TYPE_OTHER => 'Other',
            default => 'Home',
        };
    }

    public static function types(): array
    {
        return [
            self::TYPE_HOME => 'Home',
            self::TYPE_OFFICE => 'Office',
            self::TYPE_OTHER => 'Other',
        ];
    }
}

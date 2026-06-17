<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Coupon extends Model
{
    public const AUDIENCE_PUBLIC = 'public';

    public const AUDIENCE_CUSTOMERS = 'customers';

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'label',
        'discount_type',
        'discount_value',
        'audience',
        'starts_at',
        'ends_at',
        'max_uses',
        'per_customer_limit',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'max_uses' => 'integer',
            'per_customer_limit' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_customer')->withTimestamps();
    }

    public function isPublic(): bool
    {
        return $this->audience === self::AUDIENCE_PUBLIC;
    }

    public function totalUses(): int
    {
        return Order::query()
            ->where('coupon_code', $this->code)
            ->count();
    }

    public function usesByCustomer(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return Order::query()
            ->where('coupon_code', $this->code)
            ->where('user_id', $user->id)
            ->count();
    }

    public function discountAmount(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0;
        }

        $value = (float) $this->discount_value;
        $amount = match ($this->discount_type) {
            self::TYPE_PERCENT => $subtotal * ($value / 100),
            self::TYPE_FIXED => $value,
            default => 0,
        };

        return round(min($amount, $subtotal), 2);
    }

    public function toCartPayload(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->discount_type,
            'value' => (float) $this->discount_value,
            'label' => $this->label ?: $this->defaultLabel(),
        ];
    }

    private function defaultLabel(): string
    {
        if ($this->discount_type === self::TYPE_PERCENT) {
            return rtrim(rtrim((string) $this->discount_value, '0'), '.').'% off your order';
        }

        return money($this->discount_value).' off your order';
    }
}

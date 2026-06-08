<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'number', 'order_type', 'customer_name', 'customer_email', 'customer_phone',
        'address', 'city', 'zip', 'payment_method', 'reference_code', 'bank_name',
        'payment_screenshot', 'notes', 'subtotal', 'discount', 'shipping', 'total',
        'coupon_code', 'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isCustom(): bool
    {
        return $this->order_type === 'custom';
    }

    public function isProcessed(): bool
    {
        return in_array($this->status, ['processing', 'shipped', 'delivered', 'completed'], true);
    }

    public function canBeDeleted(): bool
    {
        return ! $this->isProcessed();
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cod' => 'COD (Cash on Delivery)',
            'bank_transfer' => 'Bank Transfer',
            'order_code' => 'COD',
            default => ucfirst(str_replace('_', ' ', $this->payment_method ?? 'card')),
        };
    }

    public function paymentScreenshotUrl(): ?string
    {
        if (! $this->payment_screenshot) {
            return null;
        }

        return app(\App\Services\MediaStorageService::class)->url($this->payment_screenshot);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'processing' => 'bg-blue-100 text-blue-700',
            'shipped' => 'bg-purple-100 text-purple-700',
            'delivered' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }
}

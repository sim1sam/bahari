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
        'coupon_code', 'status', 'payment_status', 'amount_paid',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
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

    public function payments(): HasMany
    {
        return $this->hasMany(OrderPayment::class)->latest();
    }

    public function amountDue(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    public function recalculatePaymentStatus(): void
    {
        $paid = (float) $this->amount_paid;
        $total = (float) $this->total;

        if ($paid <= 0) {
            $this->payment_status = 'due';
        } elseif ($paid >= $total) {
            $this->payment_status = 'paid';
            $this->amount_paid = $total;
        } else {
            $this->payment_status = 'partial';
        }
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'paid' => 'Paid',
            'partial' => 'Partial',
            'due' => 'Due',
            default => 'Pending',
        };
    }

    public function paymentStatusColor(): string
    {
        return match ($this->payment_status) {
            'paid' => 'bg-green-100 text-green-700',
            'partial' => 'bg-amber-100 text-amber-700',
            'due' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function paymentStatusBadgeClass(): string
    {
        return match ($this->payment_status) {
            'paid' => 'badge-success',
            'partial' => 'badge-warning',
            'due' => 'badge-danger',
            default => 'badge-secondary',
        };
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

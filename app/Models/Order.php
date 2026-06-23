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
        'external_transfer_status', 'external_transfer_message', 'external_transferred_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'shipping' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'external_transferred_at' => 'datetime',
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

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class)->latest();
    }

    public function latestPaymentTransaction(): ?PaymentTransaction
    {
        return $this->paymentTransactions()->first();
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
            'sslcommerz' => 'SSLCommerz',
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
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };
    }

    /** @return array<int, array{key: string, label: string, description: string, icon: string}> */
    public static function trackingSteps(): array
    {
        return [
            ['key' => 'pending', 'label' => 'Order Placed', 'description' => 'We received your order', 'icon' => 'clipboard'],
            ['key' => 'processing', 'label' => 'Processing', 'description' => 'Your items are being prepared', 'icon' => 'cog'],
            ['key' => 'shipped', 'label' => 'Shipped', 'description' => 'Your order is on the way', 'icon' => 'truck'],
            ['key' => 'completed', 'label' => 'Completed', 'description' => 'Successfully delivered', 'icon' => 'check'],
        ];
    }

    public function trackingStepIndex(): int
    {
        return match ($this->status) {
            'processing' => 1,
            'shipped' => 2,
            'delivered', 'completed' => 3,
            'cancelled' => -1,
            default => 0,
        };
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function trackingProgressPercent(): int
    {
        if ($this->isCancelled()) {
            return 0;
        }

        $index = $this->trackingStepIndex();
        $steps = count(self::trackingSteps());

        if ($steps <= 1) {
            return 0;
        }

        return (int) round(($index / ($steps - 1)) * 100);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'processing' => 'bg-blue-100 text-blue-700',
            'shipped' => 'bg-purple-100 text-purple-700',
            'delivered', 'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }
}

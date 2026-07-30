<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'number', 'tracking_event_id', 'order_type', 'customer_name', 'customer_email', 'customer_phone',
        'address', 'city', 'zip', 'payment_method', 'reference_code', 'bank_name',
        'payment_screenshot', 'notes', 'subtotal', 'discount', 'shipping', 'shipping_zone', 'total',
        'coupon_code', 'status', 'receiver_status', 'completed_at', 'payment_status', 'amount_paid',
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
            'completed_at' => 'datetime',
            'external_transferred_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order) {
            if ($order->status === 'completed') {
                if (! $order->completed_at) {
                    $order->completed_at = now();
                }
            } elseif ($order->isDirty('status')) {
                $order->completed_at = null;
            }
        });
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

    public function canAcceptPayment(): bool
    {
        if ($this->amountDue() <= 0 || $this->isCancelled()) {
            return false;
        }

        $transaction = $this->relationLoaded('paymentTransactions')
            ? $this->paymentTransactions->first()
            : $this->latestPaymentTransaction();

        return ! $transaction?->isPending();
    }

    public function canPay(): bool
    {
        if (! $this->canAcceptPayment()) {
            return false;
        }

        if (app(\App\Services\SiteSettingsService::class)->sslCommerzConfigured()) {
            return true;
        }

        return PaymentBank::query()->where('is_active', true)->exists();
    }

    public function canPayOnline(): bool
    {
        if (! $this->canAcceptPayment()) {
            return false;
        }

        return app(\App\Services\SiteSettingsService::class)->sslCommerzConfigured();
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
        return in_array($this->customerFacingStatus(), ['processing', 'shipped', 'delivered', 'completed'], true);
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

    /**
     * Customer-facing status key (for timeline / colors).
     */
    public function customerFacingStatus(): string
    {
        if (filled($this->receiver_status)) {
            return self::mapReceiverStatusToLocal($this->receiver_status);
        }

        return self::mapReceiverStatusToLocal($this->status);
    }

    /**
     * Admin display — exact receiver status when present.
     */
    public function adminStatusLabel(): string
    {
        if (filled($this->receiver_status)) {
            return self::formatStatusLabel($this->receiver_status);
        }

        return $this->statusLabel();
    }

    /**
     * Single status label for admin + customer (same text).
     * Prefers API/receiver workflow status when present.
     */
    public function statusLabel(): string
    {
        if (filled($this->receiver_status)) {
            return self::formatStatusLabel($this->receiver_status);
        }

        return match ($this->customerFacingStatus()) {
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'confirmed' => 'Confirmed',
            default => 'Pending',
        };
    }

    /**
     * Value selected in the unified admin status dropdown.
     */
    public function workflowStatusValue(): string
    {
        if (filled($this->receiver_status)) {
            return (string) $this->receiver_status;
        }

        return (string) ($this->status ?: 'pending');
    }

    /**
     * One status list for admin (Processing = transfer; others sync to customer).
     *
     * @return array<string, string>
     */
    public static function workflowStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'processing' => 'Processing (transfer to API)',
            'confirmed' => 'Confirmed',
            'kolkata_warehouse' => 'Kolkata Warehouse',
            'shipped' => 'Shipped',
            'dhaka_warehouse' => 'Dhaka Warehouse',
            'ready_for_delivery' => 'Ready for Delivery',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
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

    /**
     * Receiver transfer workflow shown to customers after status sync.
     *
     * @return array<int, array{key: string, label: string, description: string, icon: string}>
     */
    public static function receiverTrackingSteps(): array
    {
        return [
            ['key' => 'pending', 'label' => 'Pending', 'description' => 'Order received', 'icon' => 'clipboard'],
            ['key' => 'confirmed', 'label' => 'Confirmed', 'description' => 'Order confirmed', 'icon' => 'cog'],
            ['key' => 'kolkata_warehouse', 'label' => 'Kolkata Warehouse', 'description' => 'At Kolkata warehouse', 'icon' => 'cog'],
            ['key' => 'shipped', 'label' => 'Shipped', 'description' => 'Shipped toward Dhaka', 'icon' => 'truck'],
            ['key' => 'dhaka_warehouse', 'label' => 'Dhaka Warehouse', 'description' => 'At Dhaka warehouse', 'icon' => 'truck'],
            ['key' => 'ready_for_delivery', 'label' => 'Ready for Delivery', 'description' => 'Ready for local delivery', 'icon' => 'truck'],
            ['key' => 'dispatched', 'label' => 'Dispatched', 'description' => 'Out for delivery', 'icon' => 'truck'],
            ['key' => 'delivered', 'label' => 'Delivered', 'description' => 'Delivered successfully', 'icon' => 'check'],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, description: string, icon: string}>
     */
    public function trackingStepsForDisplay(): array
    {
        if (filled($this->receiver_status) || filled($this->external_transferred_at)) {
            return self::receiverTrackingSteps();
        }

        return self::trackingSteps();
    }

    public function trackingStepIndex(): int
    {
        if (filled($this->receiver_status) || filled($this->external_transferred_at)) {
            $key = self::normalizeStatusKey($this->receiver_status ?: $this->status);

            if (in_array($key, ['cancelled', 'canceled', 'cancel'], true)) {
                return -1;
            }

            $keys = array_column(self::receiverTrackingSteps(), 'key');
            $idx = array_search($key, $keys, true);

            if ($idx === false) {
                // Fall back to mapped local step inside receiver timeline.
                $mapped = self::mapReceiverStatusToLocal($key);

                return match ($mapped) {
                    'processing' => 1,
                    'shipped' => 3,
                    'delivered', 'completed' => 7,
                    default => 0,
                };
            }

            return (int) $idx;
        }

        return match ($this->customerFacingStatus()) {
            'processing' => 1,
            'shipped' => 2,
            'delivered', 'completed' => 3,
            'cancelled' => -1,
            default => 0,
        };
    }

    public function isCancelled(): bool
    {
        return $this->customerFacingStatus() === 'cancelled';
    }

    public function trackingProgressPercent(): int
    {
        if ($this->isCancelled()) {
            return 0;
        }

        $index = $this->trackingStepIndex();
        $steps = count($this->trackingStepsForDisplay());

        if ($steps <= 1 || $index < 0) {
            return 0;
        }

        return (int) round(($index / ($steps - 1)) * 100);
    }

    public function statusColor(): string
    {
        return match ($this->customerFacingStatus()) {
            'processing', 'confirmed' => 'bg-blue-100 text-blue-700',
            'shipped' => 'bg-purple-100 text-purple-700',
            'delivered', 'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    public function adminStatusStyleKey(): string
    {
        if (filled($this->receiver_status)) {
            return self::mapReceiverStatusToLocal($this->receiver_status);
        }

        return $this->customerFacingStatus();
    }

    /**
     * Map receiver workflow status → suggested customer tracking status.
     */
    public static function mapReceiverStatusToLocal(?string $status): string
    {
        $key = self::normalizeStatusKey($status);

        return match (true) {
            in_array($key, ['cancelled', 'canceled', 'cancel'], true) => 'cancelled',
            in_array($key, ['completed', 'complete', 'delivered', 'delivery', 'done'], true) => 'completed',
            in_array($key, [
                'shipped', 'shipping', 'ship', 'in_transit', 'transit',
                'ship_to_dhaka', 'shipped_to_dhaka', 'to_dhaka', 'dhaka',
                'warehouse', 'in_warehouse', 'dhaka_warehouse', 'kolkata_warehouse',
                'ready_for_delivery', 'ready_delivery',
                'parcel', 'parcel_ready', 'ready_parcel',
                'parcel_dispatch', 'parcel_dispatched', 'parcel_dispatching',
                'dispatch', 'dispatched', 'dispatching', 'courier', 'courier_dispatch',
            ], true) => 'shipped',
            in_array($key, [
                'processing', 'process', 'confirmed', 'confirm',
                'purchase', 'purchased', 'purchasing',
                'receiving', 'received', 'receive', 'reciving', 'reciveing',
                'ordered', 'buying', 'sourcing',
            ], true) => 'processing',
            in_array($key, ['pending', 'new', 'placed', 'order_placed'], true) => 'pending',
            default => in_array($key, ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'], true)
                ? $key
                : (filled($key) ? 'processing' : 'pending'),
        };
    }

    public static function normalizeStatusKey(?string $status): string
    {
        $status = strtolower(trim((string) $status));
        $status = str_replace(['-', '/', '\\'], ' ', $status);
        $status = preg_replace('/\s+/', '_', $status) ?? $status;

        return trim($status, '_');
    }

    public static function formatStatusLabel(?string $status): string
    {
        $status = trim((string) $status);

        if ($status === '') {
            return 'Pending';
        }

        $normalized = self::normalizeStatusKey($status);

        return match ($normalized) {
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'kolkata_warehouse' => 'Kolkata Warehouse',
            'shipped' => 'Shipped',
            'dhaka_warehouse' => 'Dhaka Warehouse',
            'ready_for_delivery' => 'Ready for Delivery',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
            'cancelled', 'canceled' => 'Cancelled',
            'parcel_dispatch', 'parcel_dispatched' => 'Parcel Dispatch',
            'ship_to_dhaka', 'shipped_to_dhaka' => 'Ship to Dhaka',
            default => Str::title(str_replace(['_', '-'], ' ', $status)),
        };
    }

    /**
     * Find order by number used in transfer/status sync.
     * Accepts BF-..., OR-BF-..., ORD-..., INV-BF-... from receiver sites.
     */
    public static function findByTransferNumber(string $orderNumber): ?self
    {
        $raw = trim($orderNumber);

        if ($raw === '') {
            return null;
        }

        $exact = static::query()->where('number', $raw)->first();
        if ($exact) {
            return $exact;
        }

        $stripped = preg_replace('/^(OR-|ORD-|INV-)+/i', '', $raw) ?: $raw;
        if ($stripped !== $raw) {
            $byStripped = static::query()->where('number', $stripped)->first();
            if ($byStripped) {
                return $byStripped;
            }
        }

        if (preg_match('/(BF-[A-Z0-9]+)/i', $raw, $matches)) {
            $byBf = static::query()->where('number', $matches[1])->first();
            if ($byBf) {
                return $byBf;
            }
        }

        return static::query()
            ->where('number', 'like', '%'.$stripped)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Normalize receiver admin_status (slug or label) to canonical key.
     */
    public static function normalizeAdminStatus(?string $status): ?string
    {
        $key = self::normalizeStatusKey($status);

        if ($key === '') {
            return null;
        }

        $aliases = [
            'kolkata' => 'kolkata_warehouse',
            'kolkata_ware_house' => 'kolkata_warehouse',
            'at_kolkata_warehouse' => 'kolkata_warehouse',
            'dhaka' => 'dhaka_warehouse',
            'at_dhaka_warehouse' => 'dhaka_warehouse',
            'ready_delivery' => 'ready_for_delivery',
            'ready' => 'ready_for_delivery',
            'out_for_delivery' => 'dispatched',
            'canceled' => 'cancelled',
            'cancel' => 'cancelled',
            'complete' => 'delivered',
            'completed' => 'delivered',
            'confirm' => 'confirmed',
        ];

        return $aliases[$key] ?? $key;
    }

    /**
     * Receiver API update: store exact admin_status and sync customer-visible status.
     */
    public function applyReceiverStatusUpdate(string $receiverStatus, ?string $paymentStatus = null, ?float $amountPaid = null, ?string $message = null): void
    {
        $normalized = self::normalizeAdminStatus($receiverStatus) ?: trim($receiverStatus);
        $this->receiver_status = $normalized;

        // Keep customer tracking in sync with receiver admin status.
        $this->status = self::mapReceiverStatusToLocal($normalized);

        if (in_array($this->status, ['completed', 'delivered'], true) && ! $this->completed_at) {
            $this->completed_at = now();
        }

        if ($paymentStatus !== null) {
            $this->payment_status = self::mapReceiverPaymentStatus($paymentStatus);
        }

        if ($amountPaid !== null) {
            $this->amount_paid = min(round($amountPaid, 2), (float) $this->total);
        }

        if ($message !== null) {
            $this->external_transfer_message = $message;
        }
    }

    public static function mapReceiverPaymentStatus(?string $status): string
    {
        $key = self::normalizeStatusKey($status);

        return match ($key) {
            'paid', 'complete', 'completed' => 'paid',
            'partial', 'part_paid', 'partially_paid' => 'partial',
            'due', 'unpaid', 'cod' => 'due',
            'pending' => 'pending',
            default => 'pending',
        };
    }
}

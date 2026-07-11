<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderPayment extends Model
{
    protected $fillable = [
        'order_id', 'recorded_by', 'payment_bank_id', 'payment_transaction_id',
        'amount', 'sale_amount', 'bank_charge_percent', 'bank_charge_amount',
        'payment_method', 'bank_name', 'screenshot', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sale_amount' => 'decimal:2',
            'bank_charge_percent' => 'decimal:2',
            'bank_charge_amount' => 'decimal:2',
        ];
    }

    public function paymentBank(): BelongsTo
    {
        return $this->belongsTo(PaymentBank::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function methodLabel(): string
    {
        return match ($this->payment_method) {
            'cod' => 'COD',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    public function screenshotUrl(): ?string
    {
        if (! $this->screenshot) {
            return null;
        }

        return app(\App\Services\MediaStorageService::class)->url($this->screenshot);
    }
}

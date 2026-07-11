<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPayment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'payment_bank_id',
        'recorded_by',
        'amount',
        'sale_amount',
        'bank_charge_percent',
        'bank_charge_amount',
        'bank_name',
        'notes',
        'payment_date',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'sale_amount' => 'decimal:2',
            'bank_charge_percent' => 'decimal:2',
            'bank_charge_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentBank(): BelongsTo
    {
        return $this->belongsTo(PaymentBank::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}

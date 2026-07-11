<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    public const TYPE_PAYMENT_IN = 'payment_in';

    public const TYPE_PAYMENT_OUT = 'payment_out';

    public const TYPE_ADVANCE_IN = 'advance_in';

    public const TYPE_GATEWAY_IN = 'gateway_in';

    public const TYPE_CHECKOUT_PENDING = 'checkout_pending';

    public const TYPE_INTER_TRANSFER_IN = 'inter_transfer_in';

    public const TYPE_INTER_TRANSFER_OUT = 'inter_transfer_out';

    public const DIRECTION_CREDIT = 'credit';

    public const DIRECTION_DEBIT = 'debit';

    /** Types excluded from P&L calculations. */
    public const PNL_EXCLUDED_TYPES = [
        self::TYPE_INTER_TRANSFER_IN,
        self::TYPE_INTER_TRANSFER_OUT,
        self::TYPE_CHECKOUT_PENDING,
    ];

    protected $fillable = [
        'transaction_date',
        'type',
        'direction',
        'source_type',
        'source_id',
        'order_id',
        'user_id',
        'payment_bank_id',
        'counterparty_bank_id',
        'account_head_id',
        'base_amount',
        'bank_charge_percent',
        'bank_charge_amount',
        'total_amount',
        'reference',
        'description',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'base_amount' => 'decimal:2',
            'bank_charge_percent' => 'decimal:2',
            'bank_charge_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentBank(): BelongsTo
    {
        return $this->belongsTo(PaymentBank::class);
    }

    public function counterpartyBank(): BelongsTo
    {
        return $this->belongsTo(PaymentBank::class, 'counterparty_bank_id');
    }

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(AccountHead::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_PAYMENT_IN => 'Payment In',
            self::TYPE_PAYMENT_OUT => 'Expense',
            self::TYPE_ADVANCE_IN => 'Advance Payment',
            self::TYPE_GATEWAY_IN => 'Gateway Payment',
            self::TYPE_CHECKOUT_PENDING => 'Checkout Pending',
            self::TYPE_INTER_TRANSFER_IN => 'Inter Transfer In',
            self::TYPE_INTER_TRANSFER_OUT => 'Inter Transfer Out',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }
}

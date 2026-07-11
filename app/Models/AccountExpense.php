<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountExpense extends Model
{
    protected $fillable = [
        'expense_date',
        'account_head_id',
        'title',
        'notes',
        'amount',
        'payment_bank_id',
        'bank_charge_percent',
        'bank_charge_amount',
        'total_deduction',
        'payment_method',
        'reference',
        'product_id',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'bank_charge_percent' => 'decimal:2',
            'bank_charge_amount' => 'decimal:2',
            'total_deduction' => 'decimal:2',
        ];
    }

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(AccountHead::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function paymentBank(): BelongsTo
    {
        return $this->belongsTo(PaymentBank::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function accountHeadLabel(): string
    {
        return $this->accountHead?->displayName() ?? '—';
    }

    public function isInventoryPurchase(): bool
    {
        return $this->product_id !== null
            || strtoupper((string) $this->accountHead?->code) === 'INVENTORY';
    }
}

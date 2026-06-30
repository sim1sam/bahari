<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountExpense extends Model
{
    public const CATEGORIES = [
        'inventory' => 'Product Purchase / Inventory',
        'rent' => 'Rent',
        'salary' => 'Salary & Wages',
        'marketing' => 'Marketing',
        'shipping' => 'Shipping & Delivery',
        'utilities' => 'Utilities',
        'supplies' => 'Supplies',
        'maintenance' => 'Maintenance',
        'other' => 'Other',
    ];

    protected $fillable = [
        'expense_date',
        'category',
        'title',
        'notes',
        'amount',
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
        ];
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }
}

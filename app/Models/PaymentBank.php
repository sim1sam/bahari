<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentBank extends Model
{
    protected $fillable = [
        'name',
        'account_name',
        'account_number',
        'branch',
        'instructions',
        'charge_percent',
        'opening_balance',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'charge_percent' => 'decimal:2',
            'opening_balance' => 'decimal:2',
        ];
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(AccountExpense::class);
    }

    public function imageUrl(): ?string
    {
        return app(MediaStorageService::class)->url($this->image);
    }

    public function displayName(): string
    {
        return trim($this->name.($this->account_number ? ' - '.$this->account_number : ''));
    }

    public static function activeForCheckout()
    {
        return self::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}

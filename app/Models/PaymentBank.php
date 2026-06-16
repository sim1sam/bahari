<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Model;

class PaymentBank extends Model
{
    protected $fillable = [
        'name',
        'account_name',
        'account_number',
        'branch',
        'instructions',
        'image',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountHead extends Model
{
    protected $fillable = [
        'name',
        'code',
        'account_head_type_id',
        'description',
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

    public function accountHeadType(): BelongsTo
    {
        return $this->belongsTo(AccountHeadType::class);
    }

    public function typeLabel(): string
    {
        return $this->accountHeadType?->name ?? '—';
    }

    public function displayName(): string
    {
        return filled($this->code)
            ? "{$this->code} — {$this->name}"
            : $this->name;
    }
}

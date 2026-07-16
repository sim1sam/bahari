<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ShopPageBrandSchedule extends Model
{
    protected $fillable = [
        'brand',
        'starts_at',
        'ends_at',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('brand');
    }

    public function scopeCurrentlyActive(Builder $query, ?Carbon $on = null): Builder
    {
        $on = ($on ?? now())->toDateString();

        return $query->where('is_active', true)
            ->where(function (Builder $builder) use ($on) {
                $builder->whereNull('starts_at')->orWhereDate('starts_at', '<=', $on);
            })
            ->where(function (Builder $builder) use ($on) {
                $builder->whereNull('ends_at')->orWhereDate('ends_at', '>=', $on);
            });
    }

    public function isCurrentlyActive(?Carbon $on = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $on = $on ?? now();

        if ($this->starts_at && $on->lt($this->starts_at->startOfDay())) {
            return false;
        }

        if ($this->ends_at && $on->gt($this->ends_at->endOfDay())) {
            return false;
        }

        return true;
    }
}

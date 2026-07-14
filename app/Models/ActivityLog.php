<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_email',
        'action',
        'description',
        'method',
        'route_name',
        'url',
        'ip_address',
        'user_agent',
        'properties',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actorLabel(): string
    {
        if ($this->user_name) {
            return $this->user_name;
        }

        return $this->user?->name ?? 'System';
    }

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'login' => 'badge-success',
            'logout' => 'badge-secondary',
            'created', 'store' => 'badge-primary',
            'updated', 'update' => 'badge-info',
            'deleted', 'destroy' => 'badge-danger',
            'synced', 'sync', 'approved', 'approve' => 'badge-warning',
            default => 'badge-light',
        };
    }
}

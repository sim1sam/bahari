<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'avatar', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function defaultAddress(): ?CustomerAddress
    {
        return $this->addresses()
            ->where('is_default', true)
            ->first() ?? $this->addresses()->oldest()->first();
    }

    public function hasActiveRole(): bool
    {
        return (bool) $this->role?->is_active;
    }

    public function isAdmin(): bool
    {
        return $this->hasActiveRole() && (bool) $this->role?->can_access_admin;
    }

    public function canAccessAdminFeature(string $feature): bool
    {
        return $this->isAdmin() && $this->role->hasFeature($feature);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function scopeCustomers($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('slug', Role::SLUG_CUSTOMER));
    }

    public function scopeStaff($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('can_access_admin', true));
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return app(\App\Services\MediaStorageService::class)->url($this->avatar);
    }

    public function initials(): string
    {
        return strtoupper(substr($this->name, 0, 1));
    }
}

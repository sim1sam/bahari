<?php

namespace App\Models;

use App\Services\ApiReceivedMetadataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class ApiReceivedItem extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_IMPORTED = 'imported';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'api_source_id', 'source_id', 'sku', 'slug', 'title', 'price', 'original_price',
        'image', 'processed_image', 'images', 'description', 'category_name', 'brand', 'vendor',
        'sizes', 'colors', 'badge', 'badge_variant', 'rating',
        'payload', 'status', 'product_id', 'reviewed_by', 'reviewed_at', 'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'rating' => 'decimal:1',
            'images' => 'array',
            'sizes' => 'array',
            'colors' => 'array',
            'payload' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function payloadData(): array
    {
        return is_array($this->payload) ? $this->payload : [];
    }

    public static function hasBrandVendorColumns(): bool
    {
        static $hasColumns = null;

        return $hasColumns ??= Schema::hasColumn((new self)->getTable(), 'brand');
    }

    /** @param array<string, mixed> $attributes */
    public static function withoutMissingBrandVendorColumns(array $attributes): array
    {
        if (self::hasBrandVendorColumns()) {
            return $attributes;
        }

        unset($attributes['brand'], $attributes['vendor']);

        return $attributes;
    }

    public function getBrandAttribute(?string $value): ?string
    {
        if (filled($value)) {
            return $value;
        }

        return app(ApiReceivedMetadataService::class)->extract($this->payloadData())['brand'];
    }

    public function getVendorAttribute(?string $value): ?string
    {
        if (filled($value)) {
            return $value;
        }

        return app(ApiReceivedMetadataService::class)->extract($this->payloadData())['vendor'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(ApiSource::class, 'api_source_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessed(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function isImported(): bool
    {
        return $this->status === self::STATUS_IMPORTED;
    }

    public function canProcess(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSED], true);
    }

    public function canPublish(): bool
    {
        return $this->status === self::STATUS_PROCESSED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSED => 'Processed',
            self::STATUS_IMPORTED => 'Published',
            self::STATUS_REJECTED => 'Rejected',
            default => 'Pending',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PROCESSED => 'badge-info',
            self::STATUS_IMPORTED => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            default => 'badge-warning',
        };
    }

    public function imageUrl(): ?string
    {
        return app(\App\Services\ApiReceivedImageService::class)->displayUrl($this);
    }

    public function processedImageUrl(): ?string
    {
        return $this->resolveMediaUrl($this->processed_image);
    }

    public function displayImageUrl(): ?string
    {
        if ($url = $this->processedImageUrl()) {
            return $url;
        }

        return $this->imageUrl();
    }

    private function resolveMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return app(\App\Services\MediaStorageService::class)->url($path);
    }
}

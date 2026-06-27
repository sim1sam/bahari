<?php

namespace App\Services;

use App\Models\ApiReceivedItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApiReceivedImageService
{
    public function __construct(private MediaStorageService $media) {}

    /** @return array{image: ?string, images: array<int, string>} */
    public function ingestFromItemData(array $data, ?string $sourceBaseUrl = null): array
    {
        $baseUrl = $this->resolveBaseUrl($data, $sourceBaseUrl);
        $gallery = [];

        foreach ($this->collectImageCandidates($data) as $candidate) {
            $stored = $this->storeCandidate($candidate, $baseUrl);

            if ($stored) {
                $gallery[] = $stored;
            }
        }

        $gallery = array_values(array_unique($gallery));
        $primary = $gallery[0] ?? null;

        return [
            'image' => $primary,
            'images' => $gallery ?: ($primary ? [$primary] : []),
        ];
    }

    public function displayUrl(ApiReceivedItem $item): ?string
    {
        foreach ($this->storedPaths($item) as $path) {
            if ($url = $this->media->url($path)) {
                return $url;
            }
        }

        $baseUrl = $item->source?->base_url;
        $payload = $item->payloadData();

        foreach ($this->collectImageCandidates($payload) as $candidate) {
            $absolute = $this->toAbsoluteUrl($candidate, $baseUrl, $payload);

            if ($absolute && $this->media->isExternal($absolute)) {
                return $absolute;
            }
        }

        return null;
    }

    public function repairItem(ApiReceivedItem $item): bool
    {
        $payload = $item->payloadData();

        if ($payload === []) {
            return false;
        }

        $result = $this->ingestFromItemData($payload, $item->source?->base_url);

        if (! $result['image']) {
            return false;
        }

        $item->update([
            'image' => $result['image'],
            'images' => $result['images'],
        ]);

        return true;
    }

    public function resolveProcessableImagePath(ApiReceivedItem $item): ?string
    {
        $item->loadMissing('source');

        foreach ($this->storedPaths($item) as $path) {
            if ($local = $this->resolveToLocalPath($path, $item)) {
                return $local;
            }
        }

        if ($item->payloadData() !== [] && $this->repairItem($item)) {
            $item->refresh();

            foreach ($this->storedPaths($item) as $path) {
                if ($local = $this->resolveToLocalPath($path, $item)) {
                    return $local;
                }
            }
        }

        return null;
    }

    public function persistLocalImage(ApiReceivedItem $item, string $localPath): string
    {
        $storedPath = $this->ensurePublicStoragePath($localPath, 'api-received', $item->image);

        if (! $storedPath) {
            throw new \RuntimeException('Could not store product image on disk.');
        }

        $gallery = array_values(array_unique(array_filter(array_merge(
            $item->images ?? [],
            [$storedPath]
        ))));

        $item->update([
            'image' => $storedPath,
            'images' => $gallery,
        ]);

        return $storedPath;
    }

    public function recordProcessedImage(ApiReceivedItem $item, string $processedPath, ?string $sourcePath = null): void
    {
        $processedPath = $this->media->storedPath($processedPath) ?? $processedPath;

        if (! Storage::disk('public')->exists($processedPath)) {
            throw new \RuntimeException('Processed image file was not saved to storage.');
        }

        $sourcePath = $this->media->storedPath($sourcePath ?? $item->image) ?? $sourcePath;
        $gallery = array_values(array_unique(array_filter([
            $sourcePath,
            $processedPath,
        ])));

        $attributes = [
            'processed_image' => $processedPath,
            'image' => $processedPath,
            'images' => $gallery,
            'status' => ApiReceivedItem::STATUS_PROCESSED,
        ];

        if (ApiReceivedItem::hasProcessedImageBlobColumn()) {
            $attributes['processed_image_blob'] = null;
        }

        $item->update($attributes);
    }

    private function ensurePublicStoragePath(string $path, string $directory, ?string $current = null): ?string
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        $stored = $this->media->storedPath($path);

        if ($stored && Storage::disk('public')->exists($stored)) {
            return $stored;
        }

        if ($this->media->isExternal($path)) {
            try {
                return $this->media->storeFromUrl($path, $directory, $current);
            } catch (\Throwable) {
                return null;
            }
        }

        if (is_file($path) && is_readable($path)) {
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
            $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
                ? ($extension === 'jpeg' ? 'jpg' : $extension)
                : 'jpg';
            $destination = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

            Storage::disk('public')->makeDirectory($directory);

            if (Storage::disk('public')->put($destination, file_get_contents($path))) {
                return $destination;
            }
        }

        return $stored;
    }

    /** @return array<int, string> */
    private function collectImageCandidates(array $data): array
    {
        $candidates = [];

        foreach (['image_url', 'image', 'thumbnail', 'photo', 'featured_image', 'picture', 'img'] as $key) {
            if (array_key_exists($key, $data)) {
                $extracted = $this->extractImageValue($data[$key]);

                if ($extracted) {
                    $candidates[] = $extracted;
                }
            }
        }

        foreach ($data['images'] ?? [] as $image) {
            $extracted = $this->extractImageValue($image);

            if ($extracted) {
                $candidates[] = $extracted;
            }
        }

        return array_values(array_unique(array_filter($candidates)));
    }

    private function extractImageValue(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value !== '' ? $value : null;
        }

        if (is_array($value)) {
            return $this->extractImageValue(
                $value['url']
                    ?? $value['src']
                    ?? $value['path']
                    ?? $value['image']
                    ?? $value['image_url']
                    ?? ($value[0] ?? null)
            );
        }

        return null;
    }

    private function resolveBaseUrl(array $data, ?string $sourceBaseUrl): ?string
    {
        foreach (['base_url', 'site_url', 'origin', 'source_url', 'app_url', 'sender_url'] as $key) {
            if (! empty($data[$key]) && is_string($data[$key])) {
                return rtrim(trim($data[$key]), '/');
            }
        }

        return $sourceBaseUrl ? rtrim(trim($sourceBaseUrl), '/') : null;
    }

    private function toAbsoluteUrl(string $url, ?string $baseUrl, array $data = []): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        $base = $this->resolveBaseUrl($data, $baseUrl);

        if (! $base) {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return $base.$url;
        }

        return $base.'/'.ltrim($url, '/');
    }

    private function storeCandidate(string $candidate, ?string $baseUrl): ?string
    {
        $candidate = trim($candidate);

        if ($candidate === '') {
            return null;
        }

        if (str_starts_with($candidate, 'data:image/')) {
            try {
                return $this->media->storeFromDataUri($candidate, 'api-received');
            } catch (\Throwable) {
                return null;
            }
        }

        $absolute = $this->toAbsoluteUrl($candidate, $baseUrl);

        if ($absolute && $this->media->isExternal($absolute)) {
            try {
                return $this->media->storeFromUrl($absolute, 'api-received');
            } catch (\Throwable) {
                return $absolute;
            }
        }

        $stored = $this->media->storedPath($candidate);

        if ($stored && \Illuminate\Support\Facades\Storage::disk('public')->exists($stored)) {
            return $stored;
        }

        return $candidate;
    }

    private function resolveToLocalPath(string $path, ApiReceivedItem $item): ?string
    {
        $stored = $this->media->storedPath($path);

        if ($stored && \Illuminate\Support\Facades\Storage::disk('public')->exists($stored)) {
            return $stored;
        }

        if ($this->media->isExternal($path)) {
            try {
                return $this->media->storeFromUrl($path, 'api-received');
            } catch (\Throwable) {
                return null;
            }
        }

        $absolute = $this->toAbsoluteUrl($path, $item->source?->base_url, $item->payloadData());

        if ($absolute && $this->media->isExternal($absolute)) {
            try {
                return $this->media->storeFromUrl($absolute, 'api-received');
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /** @return array<int, string> */
    private function storedPaths(ApiReceivedItem $item): array
    {
        $paths = [];

        if ($item->image) {
            $paths[] = $item->image;
        }

        foreach ($item->images ?? [] as $image) {
            if (is_string($image) && $image !== '') {
                $paths[] = $image;
            }
        }

        return array_values(array_unique($paths));
    }
}

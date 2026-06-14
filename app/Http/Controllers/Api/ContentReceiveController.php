<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\MediaStorageService;

class ContentReceiveController extends Controller
{
    public function receive(Request $request, MediaStorageService $media): JsonResponse
    {
        $source = $request->attributes->get('api_source');

        $itemRules = [
            'source_id' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100',
            'title' => 'required_with:items|string|max:255',
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'category_name' => 'nullable|string|max:100',
            'sizes' => 'nullable',
            'colors' => 'nullable',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
        ];

        $validated = $request->validate(array_merge([
            'items' => 'nullable|array',
        ], collect($itemRules)->mapWithKeys(fn ($rule, $key) => ["items.*.$key" => $rule])->all(), [
            'source_id' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100',
            'title' => 'required_without:items|string|max:255',
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'category_name' => 'nullable|string|max:100',
            'sizes' => 'nullable',
            'colors' => 'nullable',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
        ]));

        $items = $validated['items'] ?? [$validated];
        $created = [];
        $updated = [];

        foreach ($items as $itemData) {
            $normalized = $this->normalizeItemData($itemData, $media);
            if (! $normalized['title']) {
                continue;
            }

            $existing = $normalized['source_id']
                ? ApiReceivedItem::where('api_source_id', $source->id)->where('source_id', $normalized['source_id'])->first()
                : null;

            if ($existing) {
                $existing->update(array_merge($normalized, [
                    'payload' => $itemData,
                    'status' => ApiReceivedItem::STATUS_PENDING,
                    'processed_image' => null,
                ]));
                $received = $existing;
                $updated[] = $received->id;
            } else {
                $received = ApiReceivedItem::create(array_merge($normalized, [
                    'api_source_id' => $source->id,
                    'payload' => $itemData,
                    'status' => ApiReceivedItem::STATUS_PENDING,
                ]));
                $created[] = $received->id;
            }
        }

        return response()->json([
            'message' => count($created).' created, '.count($updated).' updated. Awaiting logo processing.',
            'created' => $created,
            'updated' => $updated,
        ], 201);
    }

    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'ecommerce-api-receive',
        ]);
    }

    private function normalizeItemData(array $data, MediaStorageService $media): array
    {
        $title = $data['title'] ?? $data['name'] ?? null;
        $imageUrl = $data['image_url'] ?? $data['image'] ?? null;
        $imagePath = $this->storeImage($imageUrl, $media);

        $gallery = collect($data['images'] ?? [])
            ->map(fn ($url) => $this->storeImage($url, $media))
            ->filter()
            ->values()
            ->all();

        return [
            'source_id' => $data['source_id'] ?? null,
            'sku' => $data['sku'] ?? null,
            'slug' => $data['slug'] ?? null,
            'title' => $title,
            'price' => round((float) ($data['price'] ?? 0), 2),
            'original_price' => isset($data['original_price']) ? round((float) $data['original_price'], 2) : null,
            'image' => $imagePath,
            'images' => $gallery ?: ($imagePath ? [$imagePath] : []),
            'description' => $data['description'] ?? null,
            'category_name' => $data['category_name'] ?? $data['category'] ?? null,
            'sizes' => $this->normalizeList($data['sizes'] ?? null, ['XS', 'S', 'M', 'L', 'XL']),
            'colors' => $this->normalizeList($data['colors'] ?? null, ['Black', 'White', 'Rose']),
            'badge' => $data['badge'] ?? null,
            'badge_variant' => $data['badge_variant'] ?? null,
            'rating' => isset($data['rating']) ? round((float) $data['rating'], 1) : null,
        ];
    }

    private function storeImage(?string $imageUrl, MediaStorageService $media): ?string
    {
        if (! $imageUrl) {
            return null;
        }

        if (str_starts_with($imageUrl, 'http')) {
            try {
                return $media->storeFromUrl($imageUrl, 'api-received');
            } catch (\Throwable) {
                return $imageUrl;
            }
        }

        return $imageUrl;
    }

    private function normalizeList(mixed $value, array $default): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value)));
        }

        if (is_string($value) && $value !== '') {
            return array_values(array_filter(array_map('trim', explode(',', $value))));
        }

        return $default;
    }
}

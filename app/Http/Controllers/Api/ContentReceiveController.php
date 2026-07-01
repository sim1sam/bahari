<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Services\ApiReceivedImageService;
use App\Services\ApiReceivedMetadataService;
use App\Services\ApiReceivedPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentReceiveController extends Controller
{
    public function receive(Request $request, ApiReceivedImageService $images, ApiReceivedPriceService $prices): JsonResponse
    {
        $this->preprocessIncomingPrices($request, $prices);

        $source = $request->attributes->get('api_source');

        $itemRules = [
            'source_id' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100',
            'title' => 'required_with:items|string|max:255',
            'name' => 'nullable|string|max:255',
            'price_bdt' => 'nullable',
            'price' => 'nullable',
            'converted_price' => 'nullable',
            'original_price' => 'nullable',
            'converted_original_price' => 'nullable',
            'converted_mrp' => 'nullable',
            'purchase_price_bdt' => 'nullable',
            'purchase_price' => 'nullable',
            'image' => 'nullable',
            'image_url' => 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'nullable',
            'thumbnail' => 'nullable',
            'photo' => 'nullable',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'category_name' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'brand_name' => 'nullable|string|max:100',
            'vendor' => 'nullable|string|max:100',
            'vendor_name' => 'nullable|string|max:100',
            'seller' => 'nullable|string|max:100',
            'shop' => 'nullable|string|max:100',
            'store' => 'nullable|string|max:100',
            'sizes' => 'nullable',
            'colors' => 'nullable',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
            'base_url' => 'nullable|string|max:500',
            'site_url' => 'nullable|string|max:500',
        ];

        $validated = $request->validate(array_merge([
            'items' => 'nullable|array',
        ], collect($itemRules)->mapWithKeys(fn ($rule, $key) => ["items.*.$key" => $rule])->all(), [
            'source_id' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100',
            'title' => 'required_without:items|string|max:255',
            'name' => 'nullable|string|max:255',
            'price_bdt' => 'nullable',
            'price' => 'nullable',
            'converted_price' => 'nullable',
            'original_price' => 'nullable',
            'converted_original_price' => 'nullable',
            'converted_mrp' => 'nullable',
            'purchase_price_bdt' => 'nullable',
            'purchase_price' => 'nullable',
            'image' => 'nullable',
            'image_url' => 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'nullable',
            'thumbnail' => 'nullable',
            'photo' => 'nullable',
            'description' => 'nullable|string|max:5000',
            'category' => 'nullable|string|max:100',
            'category_name' => 'nullable|string|max:100',
            'brand' => 'nullable|string|max:100',
            'brand_name' => 'nullable|string|max:100',
            'vendor' => 'nullable|string|max:100',
            'vendor_name' => 'nullable|string|max:100',
            'seller' => 'nullable|string|max:100',
            'shop' => 'nullable|string|max:100',
            'store' => 'nullable|string|max:100',
            'sizes' => 'nullable',
            'colors' => 'nullable',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
            'base_url' => 'nullable|string|max:500',
            'site_url' => 'nullable|string|max:500',
        ]));

        $items = $validated['items'] ?? [$validated];
        $created = [];
        $updated = [];

        foreach ($items as $itemData) {
            $normalized = $this->normalizeItemData($itemData, $images, $prices, $source->base_url);
            if (! $normalized['title']) {
                continue;
            }

            $existing = $normalized['source_id']
                ? ApiReceivedItem::where('api_source_id', $source->id)->where('source_id', $normalized['source_id'])->first()
                : null;

            if ($existing) {
                if ($existing->isImported() && $existing->product_id) {
                    $existing->update(ApiReceivedItem::withoutMissingBrandVendorColumns([
                        'payload' => $itemData,
                        'price' => $normalized['price'],
                        'original_price' => $normalized['original_price'],
                        'purchase_price' => $normalized['purchase_price'],
                        'title' => $normalized['title'],
                        'description' => $normalized['description'],
                        'sizes' => $normalized['sizes'],
                        'colors' => $normalized['colors'],
                        'category_name' => $normalized['category_name'],
                        'brand' => $normalized['brand'] ?? $existing->brand,
                        'vendor' => $normalized['vendor'] ?? $existing->vendor,
                    ]));

                    $this->syncLiveProductFromApi($existing->fresh(['product']), $normalized);

                    $received = $existing;
                    $updated[] = $received->id;

                    continue;
                }

                $resetAttributes = [
                    'payload' => $itemData,
                    'status' => ApiReceivedItem::STATUS_PENDING,
                    'processed_image' => null,
                ];

                if (ApiReceivedItem::hasProcessedImageBlobColumn()) {
                    $resetAttributes['processed_image_blob'] = null;
                }

                $existing->update(ApiReceivedItem::withoutMissingBrandVendorColumns(array_merge($normalized, $resetAttributes)));
                $received = $existing;
                $updated[] = $received->id;
            } else {
                $received = ApiReceivedItem::create(ApiReceivedItem::withoutMissingBrandVendorColumns(array_merge($normalized, [
                    'api_source_id' => $source->id,
                    'payload' => $itemData,
                    'status' => ApiReceivedItem::STATUS_PENDING,
                ])));
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

    private function preprocessIncomingPrices(Request $request, ApiReceivedPriceService $prices): void
    {
        $payload = $request->all();

        if (isset($payload['items']) && is_array($payload['items'])) {
            $payload['items'] = array_map(
                fn (array $item) => $prices->mergeIntoItem($item),
                $payload['items']
            );
            $request->merge(['items' => $payload['items']]);

            return;
        }

        $request->merge($prices->mergeIntoItem($payload));
    }

    private function normalizeItemData(array $data, ApiReceivedImageService $images, ApiReceivedPriceService $prices, ?string $sourceBaseUrl): array
    {
        $title = $data['title'] ?? $data['name'] ?? null;
        $imageData = $images->ingestFromItemData($data, $sourceBaseUrl);
        $priceData = $prices->extract($data);
        $metadata = app(ApiReceivedMetadataService::class)->extract($data);

        return [
            'source_id' => $data['source_id'] ?? null,
            'sku' => $data['sku'] ?? null,
            'slug' => $data['slug'] ?? null,
            'title' => $title,
            'price' => $priceData['price'],
            'original_price' => $priceData['original_price'],
            'purchase_price' => $priceData['purchase_price'],
            'image' => $imageData['image'],
            'images' => $imageData['images'],
            'description' => $data['description'] ?? null,
            'category_name' => $data['category_name'] ?? $data['category'] ?? null,
            'brand' => $metadata['brand'],
            'vendor' => $metadata['vendor'],
            'sizes' => $this->normalizeSizes($data),
            'colors' => $this->normalizeColors($data),
            'badge' => $data['badge'] ?? null,
            'badge_variant' => $data['badge_variant'] ?? null,
            'rating' => isset($data['rating']) ? round((float) $data['rating'], 1) : null,
        ];
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

    /** @return array<int, string> */
    private function normalizeSizes(array $data): array
    {
        foreach (['sizes', 'size', 'size_range', 'size_text', 'sizeText', 'available_sizes', 'availableSizes'] as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $normalized = $this->normalizeList($data[$key], []);

            if ($normalized !== []) {
                return $normalized;
            }
        }

        return [];
    }

    /** @return array<int, string> */
    private function normalizeColors(array $data): array
    {
        foreach (['colors', 'color', 'colour', 'colours', 'color_name', 'colorName'] as $key) {
            if (! array_key_exists($key, $data)) {
                continue;
            }

            $normalized = $this->normalizeList($data[$key], []);

            if ($normalized !== []) {
                return $normalized;
            }
        }

        return [];
    }

    private function syncLiveProductFromApi(ApiReceivedItem $item, array $normalized): void
    {
        $product = $item->product;

        if (! $product) {
            return;
        }

        $product->update([
            'name' => $normalized['title'] ?: $product->name,
            'price' => $normalized['price'] ?? $product->price,
            'original_price' => $normalized['original_price'] ?? $product->original_price,
            'purchase_price' => $normalized['purchase_price'] ?? $product->purchase_price,
            'description' => $normalized['description'] ?: $product->description,
            'sizes' => $normalized['sizes'] ?: $product->sizes,
            'colors' => $normalized['colors'] ?: $product->colors,
        ]);
    }
}

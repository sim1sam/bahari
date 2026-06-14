<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use App\Services\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentReceiveController extends Controller
{
    public function receive(Request $request, MediaStorageService $media): JsonResponse
    {
        $source = $request->attributes->get('api_source');

        $validated = $request->validate([
            'items' => 'nullable|array',
            'items.*.source_id' => 'nullable|string|max:100',
            'items.*.sku' => 'nullable|string|max:100',
            'items.*.title' => 'required_with:items|string|max:255',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.image' => 'nullable|string|max:1000',
            'items.*.image_url' => 'nullable|string|max:1000',
            'items.*.description' => 'nullable|string|max:5000',
            'source_id' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:100',
            'title' => 'required_without:items|string|max:255',
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'image' => 'nullable|string|max:1000',
            'image_url' => 'nullable|string|max:1000',
            'description' => 'nullable|string|max:5000',
        ]);

        $items = $validated['items'] ?? [$validated];
        $created = [];
        $skipped = [];

        foreach ($items as $itemData) {
            $title = $itemData['title'] ?? $itemData['name'] ?? null;
            if (! $title) {
                continue;
            }

            $sourceId = $itemData['source_id'] ?? null;

            if ($sourceId && ApiReceivedItem::where('api_source_id', $source->id)->where('source_id', $sourceId)->exists()) {
                $skipped[] = $sourceId;

                continue;
            }

            $imageUrl = $itemData['image_url'] ?? $itemData['image'] ?? null;
            $imagePath = null;

            if ($imageUrl && str_starts_with($imageUrl, 'http')) {
                try {
                    $imagePath = $media->storeFromUrl($imageUrl, 'api-received');
                } catch (\Throwable) {
                    $imagePath = $imageUrl;
                }
            } elseif ($imageUrl) {
                $imagePath = $imageUrl;
            }

            $received = ApiReceivedItem::create([
                'api_source_id' => $source->id,
                'source_id' => $sourceId,
                'sku' => $itemData['sku'] ?? null,
                'title' => $title,
                'price' => round((float) ($itemData['price'] ?? 0), 2),
                'image' => $imagePath,
                'description' => $itemData['description'] ?? null,
                'payload' => $itemData,
                'status' => ApiReceivedItem::STATUS_PENDING,
            ]);

            $created[] = [
                'id' => $received->id,
                'source_id' => $received->source_id,
                'sku' => $received->sku,
                'status' => $received->status,
            ];
        }

        return response()->json([
            'message' => count($created).' item(s) received.',
            'created' => $created,
            'skipped' => $skipped,
        ], 201);
    }

    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'ecommerce-api-receive',
        ]);
    }
}

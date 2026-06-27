<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiReceivedItem;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ApiReceivedImageController extends Controller
{
    public function processed(ApiReceivedItem $item): Response
    {
        $path = $item->processed_image;

        if ($path && Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path), [
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        if (! ApiReceivedItem::hasProcessedImageBlobColumn()) {
            abort(404);
        }

        $blob = ApiReceivedItem::query()
            ->whereKey($item->id)
            ->value('processed_image_blob');

        if (! is_string($blob) || $blob === '') {
            abort(404);
        }

        $binary = base64_decode($blob, true);

        if ($binary === false || $binary === '') {
            abort(404);
        }

        return response($binary, 200, [
            'Content-Type' => $this->mimeTypeForPath($path),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function mimeTypeForPath(?string $path): string
    {
        $extension = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));

        return match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };
    }
}

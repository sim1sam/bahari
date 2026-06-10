<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Services\MediaStorageService;
use Illuminate\Http\Request;

trait HandlesHomepageImages
{
    protected function resolveImage(
        Request $request,
        MediaStorageService $media,
        string $folder,
        ?string $current = null,
        string $fileKey = 'image',
        string $urlKey = 'image_url',
        string $removeKey = 'remove_image',
    ): ?string {
        if ($request->boolean($removeKey)) {
            $media->delete($current);

            return null;
        }

        $file = $request->file($fileKey);

        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            return $media->storeUpload($file, $folder, $current, $fileKey);
        }

        if ($request->filled($urlKey)) {
            return $media->storeFromUrl($request->input($urlKey), $folder, $current, $urlKey);
        }

        return $media->storedPath($current);
    }
}

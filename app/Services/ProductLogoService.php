<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductLogoService
{
    public function __construct(private MediaStorageService $media) {}

    public function applyLogoToReceivedItem(string $sourceImage, ?string $logoPath = null): string
    {
        $logoPath = $logoPath ?? SiteSetting::current()->api_logo;

        if (! $logoPath) {
            throw ValidationException::withMessages([
                'logo' => 'Upload a site logo first in API Received settings.',
            ]);
        }

        $basePath = $this->resolveLocalPath($sourceImage);
        $logoLocal = $this->resolveLocalPath($logoPath);

        if (! $basePath || ! is_readable($basePath)) {
            throw ValidationException::withMessages([
                'image' => 'Could not read the product image for processing.',
            ]);
        }

        if (! $logoLocal || ! is_readable($logoLocal)) {
            throw ValidationException::withMessages([
                'logo' => 'Could not read the logo image.',
            ]);
        }

        $base = $this->loadImage($basePath);
        $logo = $this->loadImage($logoLocal);

        if (! $base || ! $logo) {
            if ($base) {
                imagedestroy($base);
            }
            if ($logo) {
                imagedestroy($logo);
            }

            throw ValidationException::withMessages([
                'image' => 'Unsupported image format. Use JPG, PNG, or WebP.',
            ]);
        }

        $baseW = imagesx($base);
        $baseH = imagesy($base);
        $logoW = imagesx($logo);
        $logoH = imagesy($logo);

        $scalePercent = $this->logoScalePercent();
        $targetLogoW = max(48, (int) round($baseW * ($scalePercent / 100)));
        $scale = $targetLogoW / $logoW;
        $targetLogoH = max(1, (int) round($logoH * $scale));

        $resizedLogo = imagecreatetruecolor($targetLogoW, $targetLogoH);
        imagealphablending($resizedLogo, false);
        imagesavealpha($resizedLogo, true);
        $transparent = imagecolorallocatealpha($resizedLogo, 0, 0, 0, 127);
        imagefill($resizedLogo, 0, 0, $transparent);
        imagecopyresampled($resizedLogo, $logo, 0, 0, 0, 0, $targetLogoW, $targetLogoH, $logoW, $logoH);

        $destX = (int) round(($baseW - $targetLogoW) / 2);
        $destY = (int) round(($baseH - $targetLogoH) / 2);

        imagealphablending($base, true);
        imagesavealpha($base, true);
        $this->imageCopyMergeAlpha($base, $resizedLogo, $destX, $destY, 0, 0, $targetLogoW, $targetLogoH, 100);

        imagedestroy($logo);
        imagedestroy($resizedLogo);

        Storage::disk('public')->makeDirectory('api-received/processed');
        $extension = strtolower(pathinfo($basePath, PATHINFO_EXTENSION)) ?: 'jpg';
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? ($extension === 'jpeg' ? 'jpg' : $extension) : 'jpg';
        $outputPath = 'api-received/processed/'.Str::uuid().'.'.$extension;
        $fullOutput = Storage::disk('public')->path($outputPath);

        $saved = match ($extension) {
            'png' => imagepng($base, $fullOutput),
            'webp' => function_exists('imagewebp') ? imagewebp($base, $fullOutput, 90) : imagejpeg($base, $fullOutput, 90),
            default => imagejpeg($base, $fullOutput, 90),
        };

        imagedestroy($base);

        if (! $saved || ! Storage::disk('public')->exists($outputPath)) {
            throw ValidationException::withMessages([
                'image' => 'Failed to save processed image to storage.',
            ]);
        }

        return $outputPath;
    }

    public function storeSiteLogo(UploadedFile $file): string
    {
        $settings = SiteSetting::current();
        $path = $this->media->storeUpload($file, 'api-received/logos', $settings->api_logo, field: 'logo');
        $settings->api_logo = $path;
        $settings->save();

        return $path;
    }

    private function logoScalePercent(): int
    {
        $default = 28;

        if (! Schema::hasColumn((new SiteSetting)->getTable(), 'api_logo_scale')) {
            return $default;
        }

        return max(10, min(50, (int) (SiteSetting::current()->api_logo_scale ?: $default)));
    }

    private function resolveLocalPath(string $path): ?string
    {
        if ($this->media->isExternal($path)) {
            try {
                return Storage::disk('public')->path(
                    $this->media->storeFromUrl($path, 'api-received/temp')
                );
            } catch (\Throwable) {
                return null;
            }
        }

        $stored = $this->media->storedPath($path);

        if ($stored && Storage::disk('public')->exists($stored)) {
            return Storage::disk('public')->path($stored);
        }

        return null;
    }

    private function loadImage(string $path)
    {
        $type = function_exists('exif_imagetype')
            ? @exif_imagetype($path)
            : $this->guessImageType($path);

        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };
    }

    private function guessImageType(string $path): int|false
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
            'webp' => defined('IMAGETYPE_WEBP') ? IMAGETYPE_WEBP : false,
            default => false,
        };
    }

    private function imageCopyMergeAlpha($dst, $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $pct): void
    {
        $cut = imagecreatetruecolor($srcW, $srcH);
        imagecopy($cut, $dst, 0, 0, $dstX, $dstY, $srcW, $srcH);
        imagecopy($cut, $src, 0, 0, $srcX, $srcY, $srcW, $srcH);
        imagecopymerge($dst, $cut, $dstX, $dstY, 0, 0, $srcW, $srcH, $pct);
        imagedestroy($cut);
    }
}

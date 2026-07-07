<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SiteSettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PwaController extends Controller
{
    public function manifest(SiteSettingsService $settings): JsonResponse
    {
        $siteName = $settings->siteName();

        $manifest = [
            'id' => url('/'),
            'name' => $siteName,
            'short_name' => Str::limit($siteName, 12, ''),
            'description' => $settings->tagline(),
            'start_url' => url('/'),
            'scope' => url('/'),
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => $settings->get()->theme_background ?? '#f8fafc',
            'theme_color' => $settings->get()->theme_primary ?? '#0891b2',
            'categories' => ['shopping', 'fashion'],
            'icons' => [
                [
                    'src' => route('pwa.icon', ['size' => 192]),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => route('pwa.icon', ['size' => 512]),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => route('pwa.icon', ['size' => 512]),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function icon(int $size, SiteSettingsService $settings): Response
    {
        $size = in_array($size, [192, 512], true) ? $size : 192;

        $logoPath = $settings->get()->logo;
        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $absolutePath = Storage::disk('public')->path($logoPath);
            $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));

            if (in_array($extension, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
                $response = $this->resizeImage($absolutePath, $size, $extension);

                if ($response !== null) {
                    return $response;
                }
            }
        }

        return $this->generateFallbackIcon($size, $settings);
    }

    private function resizeImage(string $path, int $size, string $extension): ?Response
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $source = match ($extension) {
            'png' => @imagecreatefrompng($path),
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'gif' => @imagecreatefromgif($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $source) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($size, $size);
        imagesavealpha($canvas, true);
        imagealphablending($canvas, false);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
        imagealphablending($canvas, true);

        $scale = min($size / $width, $size / $height);
        $targetWidth = (int) round($width * $scale);
        $targetHeight = (int) round($height * $scale);
        $offsetX = (int) round(($size - $targetWidth) / 2);
        $offsetY = (int) round(($size - $targetHeight) / 2);

        imagecopyresampled($canvas, $source, $offsetX, $offsetY, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        $data = $this->pngOutput($canvas);
        imagedestroy($canvas);

        if ($data === null) {
            return null;
        }

        return response($data, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function generateFallbackIcon(int $size, SiteSettingsService $settings): Response
    {
        if (! extension_loaded('gd')) {
            abort(404);
        }

        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);
        imagealphablending($img, true);

        [$r, $g, $b] = $this->hexToRgb($settings->get()->theme_primary ?? '#0891b2');
        $bg = imagecolorallocate($img, $r, $g, $b);
        imagefilledrectangle($img, 0, 0, $size, $size, $bg);

        $white = imagecolorallocate($img, 255, 255, 255);
        $initial = Str::upper(Str::substr($settings->logoInitial(), 0, 2));
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($initial);
        $textHeight = imagefontheight($font);
        $x = (int) (($size - $textWidth) / 2);
        $y = (int) (($size - $textHeight) / 2);
        imagestring($img, $font, $x, $y, $initial, $white);

        $data = $this->pngOutput($img);
        imagedestroy($img);

        return response($data ?? '', 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function pngOutput(\GdImage $image): ?string
    {
        ob_start();
        $ok = imagepng($image);

        if (! $ok) {
            ob_end_clean();

            return null;
        }

        return ob_get_clean() ?: null;
    }

    /** @return array{0: int, 1: int, 2: int} */
    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}

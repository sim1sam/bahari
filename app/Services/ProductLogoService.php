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

    public function applyLogoToReceivedItem(
        string $sourceImage,
        ?string $logoPath = null,
        ?float $price = null,
        ?string $brand = null,
        ?string $hashtags = null,
        ?string $sizes = null,
    ): string {
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

        imagealphablending($base, true);
        imagesavealpha($base, true);

        // CRM order: logo → hashtags/sizes bar → price tag
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

        $this->imageCopyMergeAlpha($base, $resizedLogo, $destX, $destY, 0, 0, $targetLogoW, $targetLogoH, 100);

        imagedestroy($logo);
        imagedestroy($resizedLogo);

        $this->addHashtagsAndSizes($base, $brand, $hashtags, $sizes);
        $this->rebrandPriceBanner($base, $price);

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

    /**
     * CRM-style bottom bar: #brand + hashtags, then size lines.
     *
     * @param  \GdImage|resource  $image
     */
    private function addHashtagsAndSizes($image, ?string $brand, ?string $hashtags, ?string $sizes): void
    {
        $brandHashtag = '';
        $brand = trim((string) $brand);
        if ($brand !== '') {
            $brandHashtag = '#'.strtolower((string) preg_replace('/\s+/', '', $brand));
        }

        $hashtagLine = trim($brandHashtag.' '.$this->formatHashtagsFromField((string) $hashtags));
        $sizeLines = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\n|\r/', (string) $sizes) ?: []
        )));

        // Comma-only size lists → single line (CRM often uses one size summary line).
        if ($sizeLines === [] && filled($sizes)) {
            $sizeLines = [trim((string) $sizes)];
        } elseif (count($sizeLines) === 1 && str_contains($sizeLines[0], ',')) {
            // keep as one line
        }

        $lines = [];
        if ($hashtagLine !== '') {
            $lines[] = $hashtagLine;
        }
        foreach ($sizeLines as $line) {
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        if ($lines === []) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $fontSize = max(12, (int) ($width / 30) + 2);
        $fontPath = $this->resolveRegularFontPath();
        $lineHeight = $fontPath ? ($fontSize + 8) : (imagefontheight(5) + 8);
        $lineCount = count($lines);
        $totalHeight = $lineCount * $lineHeight;

        $areaHeightPercent = min(30, 10 + ($lineCount * 8));
        $oldAreaHeight = (int) (($height * $areaHeightPercent) / 100);
        $oldPadding = max(0, ($oldAreaHeight - $totalHeight) / 2);
        $newPadding = max(1, (int) ($oldPadding * 0.2));
        $areaHeight = $totalHeight + (2 * $newPadding);
        $areaY = max(0, $height - $areaHeight);
        $areaX = 0;
        $areaWidth = $width;

        // Semi-transparent black bar (CRM default opacity 80%).
        $bg = imagecolorallocatealpha($image, 0, 0, 0, (int) (127 * (100 - 80) / 100));
        imagefilledrectangle($image, $areaX, $areaY, $areaX + $areaWidth, $areaY + $areaHeight, $bg);

        $white = imagecolorallocate($image, 255, 255, 255);
        $startY = $areaY + $newPadding;

        foreach ($lines as $index => $line) {
            if ($fontPath && function_exists('imagettfbbox') && function_exists('imagettftext')) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $textWidth = abs(($bbox[4] ?? 0) - ($bbox[0] ?? 0));
                $x = (int) ($areaX + (($areaWidth - $textWidth) / 2));
                $y = (int) ($startY + ($index * $lineHeight) + $fontSize);
                imagettftext($image, $fontSize, 0, $x, $y, $white, $fontPath, $line);
            } else {
                $charW = imagefontwidth(5);
                $x = (int) ($areaX + (($areaWidth - (strlen($line) * $charW)) / 2));
                $y = (int) ($startY + ($index * $lineHeight));
                imagestring($image, 5, $x, $y, $line, $white);
            }
        }
    }

    private function formatHashtagsFromField(string $hashtags): string
    {
        $tags = array_filter(array_map('trim', preg_split('/\s+/', $hashtags) ?: []));
        $formatted = [];
        foreach ($tags as $tag) {
            if ($tag === '') {
                continue;
            }
            $formatted[] = str_starts_with($tag, '#') ? $tag : '#'.$tag;
        }

        return implode(' ', $formatted);
    }

    /**
     * Draw the CRM content-processing price tag (same size/position rules).
     * Covers any existing red ribbon first, then paints the rounded BDT tag.
     *
     * @param  \GdImage|resource  $image
     */
    private function rebrandPriceBanner($image, ?float $price): void
    {
        if ($price === null || $price <= 0) {
            return;
        }

        [$r, $g, $b] = $this->primaryRgb();
        $fill = imagecolorallocate($image, $r, $g, $b);

        // Cover sender ribbon (if any) so leftovers never show through.
        $detected = $this->detectRedPriceBanner($image);
        if ($detected !== null) {
            imagefilledrectangle(
                $image,
                $detected['x1'],
                $detected['y1'],
                $detected['x2'],
                $detected['y2'],
                $fill
            );
        }

        $this->addCrmStylePriceTag($image, $this->formatBannerPrice($price));
    }

    /**
     * Same algorithm as CRM ImageProcessingService::addPriceTag —
     * text-sized rounded pill, right-aligned, ~10% from top.
     *
     * @param  \GdImage|resource  $image
     */
    private function addCrmStylePriceTag($image, string $priceText): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $fontSize = max(14, (int) ($width / 25));
        $fontPath = $this->resolveBoldFontPath();
        $padding = (int) ($width / 40);
        $positionY = 10; // CRM default: 10% from top
        [$fillR, $fillG, $fillB] = $this->primaryRgb();

        $lines = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r\n", "\n", $priceText)))));
        if ($lines === []) {
            return;
        }

        $maxTextW = 0;
        $lineHeights = [];
        foreach ($lines as $line) {
            if ($fontPath && function_exists('imagettfbbox')) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $lineW = abs(($bbox[4] ?? 0) - ($bbox[0] ?? 0));
                $lineH = abs(($bbox[5] ?? 0) - ($bbox[1] ?? 0));
            } else {
                $lineW = strlen($line) * imagefontwidth(5);
                $lineH = imagefontheight(5);
            }
            $maxTextW = max($maxTextW, $lineW);
            $lineHeights[] = $lineH;
        }

        $lineSpacing = (int) ($fontSize * 0.3);
        $totalTextH = array_sum($lineHeights) + ($lineSpacing * (count($lines) - 1));
        $tagW = (int) ($maxTextW + ($padding * 4));
        $tagH = (int) ($totalTextH + ($padding * 2));
        $tagY = (int) (($height * $positionY) / 100);
        $tagX = $width - $tagW;

        if ($tagX < 0) {
            $tagX = 10;
        }
        if ($tagX + $tagW > $width) {
            $tagX = max(0, $width - $tagW - 10);
        }
        if ($tagY < 0) {
            $tagY = 10;
        }
        if ($tagY + $tagH > $height) {
            $tagY = max(0, $height - $tagH - 10);
        }

        $this->drawRoundedRectangle($image, $tagX, $tagY, $tagW, $tagH, [$fillR, $fillG, $fillB], 10);

        $white = imagecolorallocate($image, 255, 255, 255);
        $currentY = $tagY + $padding;
        foreach ($lines as $index => $line) {
            $lineH = $lineHeights[$index];
            if ($fontPath && function_exists('imagettfbbox') && function_exists('imagettftext')) {
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $line);
                $lineW = abs(($bbox[4] ?? 0) - ($bbox[0] ?? 0));
                $textX = (int) ($tagX + ($tagW / 2) - ($lineW / 2));
                $textY = (int) ($currentY + $lineH);
                imagettftext($image, $fontSize, 0, $textX, $textY, $white, $fontPath, $line);
            } else {
                $lineW = strlen($line) * imagefontwidth(5);
                $textX = (int) ($tagX + ($tagW / 2) - ($lineW / 2));
                imagestring($image, 5, $textX, (int) $currentY, $line, $white);
            }
            $currentY += $lineH + $lineSpacing;
        }
    }

    /**
     * @param  \GdImage|resource  $image
     * @param  array{0:int,1:int,2:int}  $color
     */
    private function drawRoundedRectangle($image, int $x, int $y, int $width, int $height, array $color, int $radius): void
    {
        $bg = imagecolorallocate($image, $color[0], $color[1], $color[2]);

        if ($radius <= 0) {
            imagefilledrectangle($image, $x, $y, $x + $width, $y + $height, $bg);

            return;
        }

        imagefilledrectangle($image, $x + $radius, $y, $x + $width - $radius, $y + $height, $bg);
        imagefilledrectangle($image, $x, $y + $radius, $x + $width, $y + $height - $radius, $bg);
        imagefilledellipse($image, $x + $radius, $y + $radius, $radius * 2, $radius * 2, $bg);
        imagefilledellipse($image, $x + $width - $radius, $y + $radius, $radius * 2, $radius * 2, $bg);
        imagefilledellipse($image, $x + $radius, $y + $height - $radius, $radius * 2, $radius * 2, $bg);
        imagefilledellipse($image, $x + $width - $radius, $y + $height - $radius, $radius * 2, $radius * 2, $bg);
    }

    /**
     * @param  \GdImage|resource  $image
     * @return array{x1:int,y1:int,x2:int,y2:int}|null
     */
    private function detectRedPriceBanner($image): ?array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Keep scan tight to the original top-right ribbon.
        $scanX1 = (int) floor($width * 0.55);
        $scanY1 = 0;
        $scanX2 = $width - 1;
        $scanY2 = (int) floor($height * 0.16);

        $minX = $scanX2;
        $minY = $scanY2;
        $maxX = $scanX1;
        $maxY = $scanY1;
        $hits = 0;
        $mask = [];

        for ($y = $scanY1; $y <= $scanY2; $y++) {
            for ($x = $scanX1; $x <= $scanX2; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                if (! $this->isBannerRed($r, $g, $b)) {
                    continue;
                }

                $mask[$y][$x] = true;
                $hits++;
                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        $bannerW = $maxX - $minX + 1;
        $bannerH = $maxY - $minY + 1;
        $minHits = max(60, (int) round(($width * $height) * 0.0003));

        if ($hits < $minHits || $bannerW < (int) ($width * 0.06) || $bannerH < 6) {
            return null;
        }

        // Trim sparse anti-alias fringe so the new bar matches original ribbon size.
        $trimmed = $this->trimSparseBannerEdges($mask, $minX, $minY, $maxX, $maxY);

        return [
            'x1' => max(0, $trimmed['x1']),
            'y1' => max(0, $trimmed['y1']),
            'x2' => min($width - 1, $trimmed['x2']),
            'y2' => min($height - 1, $trimmed['y2']),
        ];
    }

    /**
     * Shrink bbox by removing edge rows/cols that are mostly empty (not solid red).
     *
     * @param  array<int, array<int, bool>>  $mask
     * @return array{x1:int,y1:int,x2:int,y2:int}
     */
    private function trimSparseBannerEdges(array $mask, int $minX, int $minY, int $maxX, int $maxY): array
    {
        $x1 = $minX;
        $y1 = $minY;
        $x2 = $maxX;
        $y2 = $maxY;

        $rowDensity = function (int $y) use ($mask, &$x1, &$x2): float {
            $width = max(1, $x2 - $x1 + 1);
            $count = 0;
            for ($x = $x1; $x <= $x2; $x++) {
                if (! empty($mask[$y][$x])) {
                    $count++;
                }
            }

            return $count / $width;
        };

        $colDensity = function (int $x) use ($mask, &$y1, &$y2): float {
            $height = max(1, $y2 - $y1 + 1);
            $count = 0;
            for ($y = $y1; $y <= $y2; $y++) {
                if (! empty($mask[$y][$x])) {
                    $count++;
                }
            }

            return $count / $height;
        };

        // Require a mostly-solid ribbon edge (original red bar is dense).
        while ($y1 < $y2 && $rowDensity($y1) < 0.35) {
            $y1++;
        }
        while ($y2 > $y1 && $rowDensity($y2) < 0.35) {
            $y2--;
        }
        while ($x1 < $x2 && $colDensity($x1) < 0.35) {
            $x1++;
        }
        while ($x2 > $x1 && $colDensity($x2) < 0.35) {
            $x2--;
        }

        // 1px only — cover JPEG edges without enlarging the bar.
        return [
            'x1' => $x1 - 1,
            'y1' => $y1,
            'x2' => $x2 + 1,
            'y2' => $y2,
        ];
    }

    private function isBannerRed(int $r, int $g, int $b): bool
    {
        // Strong solid red ribbon only (avoid pink fringe / fabric).
        if ($r < 160) {
            return false;
        }

        if ($r < $g + 55 || $r < $b + 55) {
            return false;
        }

        if ($r > 230 && $g > 180 && $b > 180) {
            return false;
        }

        return ($r - max($g, $b)) >= 45;
    }

    /**
     * @param  \GdImage|resource  $image
     * @param  array{x1:int,y1:int,x2:int,y2:int}  $box
     */
    private function drawWhitePriceText($image, array $box, string $text): void
    {
        $boxW = max(1, $box['x2'] - $box['x1'] + 1);
        $boxH = max(1, $box['y2'] - $box['y1'] + 1);
        $white = imagecolorallocate($image, 255, 255, 255);
        $font = $this->resolveBoldFontPath();

        if ($font && function_exists('imagettftext') && function_exists('imagettfbbox')) {
            // Smaller than the ribbon height so text sits centered with padding.
            $fontSize = max(8, (int) round($boxH * 0.42));
            $bbox = imagettfbbox($fontSize, 0, $font, $text);
            $textW = abs(($bbox[2] ?? 0) - ($bbox[0] ?? 0));
            $textH = abs(($bbox[7] ?? 0) - ($bbox[1] ?? 0));

            while ($fontSize > 7 && ($textW > $boxW * 0.82 || $textH > $boxH * 0.68)) {
                $fontSize--;
                $bbox = imagettfbbox($fontSize, 0, $font, $text);
                $textW = abs(($bbox[2] ?? 0) - ($bbox[0] ?? 0));
                $textH = abs(($bbox[7] ?? 0) - ($bbox[1] ?? 0));
            }

            // True center inside the banner (account for TTF baseline offsets).
            $x = (int) round($box['x1'] + ($boxW - $textW) / 2 - ($bbox[0] ?? 0));
            $ascent = abs($bbox[7] ?? $textH);
            $y = (int) round($box['y1'] + ($boxH + $ascent) / 2 - 1);

            foreach ([[-1, 0], [1, 0], [0, -1], [0, 1], [0, 0]] as [$dx, $dy]) {
                imagettftext($image, $fontSize, 0, $x + $dx, $y + $dy, $white, $font, $text);
            }

            return;
        }

        $this->drawScaledBuiltinText($image, $box, $text, $white);
    }

    /**
     * @param  \GdImage|resource  $image
     * @param  array{x1:int,y1:int,x2:int,y2:int}  $box
     */
    private function drawScaledBuiltinText($image, array $box, string $text, int $white): void
    {
        $font = 5;
        $textW = imagefontwidth($font) * strlen($text);
        $textH = imagefontheight($font);
        $boxW = max(1, $box['x2'] - $box['x1'] + 1);
        $boxH = max(1, $box['y2'] - $box['y1'] + 1);

        $scale = max(1, min(
            (int) floor(($boxW * 0.78) / max(1, $textW)),
            (int) floor(($boxH * 0.58) / max(1, $textH))
        ));

        $scaledW = $textW * $scale;
        $scaledH = $textH * $scale;

        $tmp = imagecreatetruecolor($textW, $textH);
        $bg = imagecolorallocate($tmp, 0, 0, 0);
        imagefilledrectangle($tmp, 0, 0, $textW, $textH, $bg);
        imagecolortransparent($tmp, $bg);
        $tmpWhite = imagecolorallocate($tmp, 255, 255, 255);
        imagestring($tmp, $font, 0, 0, $text, $tmpWhite);

        $destX = (int) round($box['x1'] + (($boxW - $scaledW) / 2));
        $destY = (int) round($box['y1'] + (($boxH - $scaledH) / 2));

        imagecopyresized($image, $tmp, $destX, $destY, 0, 0, $scaledW, $scaledH, $textW, $textH);
        imagedestroy($tmp);

        // Ensure the color is white after resize (copyresized can muddy it).
        unset($white);
    }

    private function formatBannerPrice(float $price): string
    {
        return 'BDT '.number_format((int) round($price), 0, '.', ',').'/-';
    }

    /** @return array{0:int,1:int,2:int} */
    private function primaryRgb(): array
    {
        $hex = SiteSetting::current()->theme_primary ?: '#0891b2';
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = '0891b2';
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function resolveBoldFontPath(): ?string
    {
        return $this->firstReadableFont([
            resource_path('fonts/trebucbd.ttf'),
            resource_path('fonts/trebuc.ttf'),
            'C:/Windows/Fonts/trebucbd.ttf',
            'C:/Windows/Fonts/TREBUCBD.TTF',
            'C:/Windows/Fonts/trebuc.ttf',
            'C:/Windows/Fonts/TREBUC.TTF',
            resource_path('fonts/DejaVuSans-Bold.ttf'),
            'C:/Windows/Fonts/arialbd.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        ]);
    }

    private function resolveRegularFontPath(): ?string
    {
        return $this->firstReadableFont([
            resource_path('fonts/trebuc.ttf'),
            resource_path('fonts/trebucbd.ttf'),
            'C:/Windows/Fonts/trebuc.ttf',
            'C:/Windows/Fonts/TREBUC.TTF',
            'C:/Windows/Fonts/arial.ttf',
            resource_path('fonts/DejaVuSans.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        ]) ?? $this->resolveBoldFontPath();
    }

    /** @param  array<int, string>  $candidates */
    private function firstReadableFont(array $candidates): ?string
    {
        foreach ($candidates as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
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

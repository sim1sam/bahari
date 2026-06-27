<?php

namespace App\Services;

use App\Models\ApiReceivedItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ProcessedImageDownloadService
{
    public function __construct(private MediaStorageService $media) {}

    public function downloadFilename(ApiReceivedItem $item): string
    {
        $path = $item->processed_image ?: $item->image ?: '';
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)
            ? ($extension === 'jpeg' ? 'jpg' : $extension)
            : 'jpg';

        $base = Str::slug($item->sku ?: $item->title ?: 'product-'.$item->id);

        return ($base !== '' ? $base : 'product-'.$item->id).'.'.$extension;
    }

    /** @return array{path: string, temporary: bool}|null */
    public function resolveDownloadablePath(ApiReceivedItem $item): ?array
    {
        $relative = $item->processed_image ?: $item->image;
        $stored = $this->media->storedPath($relative);

        if ($stored && Storage::disk('public')->exists($stored)) {
            return [
                'path' => Storage::disk('public')->path($stored),
                'temporary' => false,
            ];
        }

        $url = $item->displayImageUrl();

        if (! $url || ! $this->media->isExternal($url)) {
            return null;
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Bahari-Processed-Download/1.0'])
                ->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful() || strlen($response->body()) < 100) {
            return null;
        }

        $extension = pathinfo(parse_url($url, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
        $temporary = tempnam(sys_get_temp_dir(), 'proc_img_').'.'.$extension;

        if (file_put_contents($temporary, $response->body()) === false) {
            return null;
        }

        return [
            'path' => $temporary,
            'temporary' => true,
        ];
    }

    /** @param Collection<int, ApiReceivedItem> $items */
    public function createZip(Collection $items, string $layout = 'brand'): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('PHP Zip extension is required for bulk downloads.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'proc_zip_').'.zip';
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create download archive.');
        }

        $usedPaths = [];
        $temporaryFiles = [];
        $added = 0;

        foreach ($items as $item) {
            $resolved = $this->resolveDownloadablePath($item);

            if (! $resolved) {
                continue;
            }

            if ($resolved['temporary']) {
                $temporaryFiles[] = $resolved['path'];
            }

            $folder = $layout === 'brand' ? $this->brandFolder($item).'/' : '';
            $entryName = $this->uniqueEntryName($folder, $this->downloadFilename($item), $usedPaths);

            if ($zip->addFile($resolved['path'], $entryName)) {
                $added++;
            }
        }

        $zip->close();

        foreach ($temporaryFiles as $temporaryFile) {
            @unlink($temporaryFile);
        }

        if ($added === 0) {
            @unlink($zipPath);

            throw new \RuntimeException('No downloadable images were found for the selected items.');
        }

        return $zipPath;
    }

    private function brandFolder(ApiReceivedItem $item): string
    {
        $brand = trim((string) $item->brand);

        if ($brand === '') {
            return 'Unbranded';
        }

        $slug = Str::slug($brand);

        return $slug !== '' ? $slug : 'Unbranded';
    }

    /** @param array<int, string> $usedPaths */
    private function uniqueEntryName(string $folder, string $filename, array &$usedPaths): string
    {
        $candidate = $folder.$filename;
        $base = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $counter = 1;

        while (in_array($candidate, $usedPaths, true)) {
            $suffix = $extension !== '' ? '.'.$extension : '';
            $candidate = $folder.$base.'-'.$counter.$suffix;
            $counter++;
        }

        $usedPaths[] = $candidate;

        return $candidate;
    }
}

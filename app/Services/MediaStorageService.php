<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaStorageService
{
    public function url(?string $path): ?string
    {
        $path = $this->normalizePath($path);

        if ($path === '') {
            return null;
        }

        if ($this->isExternal($path)) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.ltrim($path, '/');
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/'.$path);
    }

    public function storedPath(?string $path): ?string
    {
        $path = $this->normalizePath($path);

        return $path === '' ? null : $path;
    }

    public function isExternal(?string $path): bool
    {
        $path = $this->normalizePath($path);

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    public function storeUpload(UploadedFile $file, string $directory, ?string $current = null, string $field = 'image'): string
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                $field => 'Upload failed: '.$this->uploadErrorMessage($file->getError()),
            ]);
        }

        $content = $this->readUploadContent($file);

        if ($content === '') {
            throw ValidationException::withMessages([
                $field => 'Could not read the uploaded file. Please try again or use an image URL.',
            ]);
        }

        Storage::disk('public')->makeDirectory($directory);
        $this->delete($current);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? ($extension === 'jpeg' ? 'jpg' : $extension) : 'jpg';
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        if (! Storage::disk('public')->put($path, $content)) {
            throw ValidationException::withMessages([
                $field => 'Failed to write image to storage. Run: php artisan storage:link',
            ]);
        }

        return $path;
    }

    public function storeFromUrl(string $url, string $directory, ?string $current = null, string $field = 'image_url'): string
    {
        $url = trim($url);

        try {
            $response = Http::timeout(15)->withOptions(['allow_redirects' => true])->get($url);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                $field => 'Could not download the image from that URL.',
            ]);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                $field => 'Could not download the image from that URL.',
            ]);
        }

        $contentType = strtolower((string) $response->header('Content-Type'));
        $body = $response->body();

        if ($body === '' || strlen($body) < 100) {
            throw ValidationException::withMessages([
                $field => 'The URL did not return a valid image file.',
            ]);
        }

        if ($contentType && ! str_starts_with($contentType, 'image/')) {
            throw ValidationException::withMessages([
                $field => 'The URL must point directly to an image file (jpg, png, webp). Website homepages will not work.',
            ]);
        }

        $extension = $this->guessExtension($url, $contentType);
        Storage::disk('public')->makeDirectory($directory);
        $this->delete($current);

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        if (! Storage::disk('public')->put($path, $body)) {
            throw ValidationException::withMessages([
                $field => 'Failed to save downloaded image to storage.',
            ]);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        $path = $this->normalizePath($path);

        if ($path === '' || $this->isExternal($path)) {
            return;
        }

        if (str_starts_with($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        Storage::disk('public')->delete($path);
    }

    private function readUploadContent(UploadedFile $file): string
    {
        $pathname = $file->getPathname();

        if ($pathname && is_readable($pathname)) {
            $content = @file_get_contents($pathname);

            if ($content !== false && $content !== '') {
                return $content;
            }
        }

        try {
            return $file->getContent();
        } catch (\Throwable) {
            return '';
        }
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temp folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'Upload blocked by server extension.',
            default => 'Unknown upload error.',
        };
    }

    private function guessExtension(string $url, string $contentType): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
        ];

        foreach ($map as $type => $ext) {
            if (str_contains($contentType, $type)) {
                return $ext;
            }
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)
            ? ($ext === 'jpeg' ? 'jpg' : $ext)
            : 'jpg';
    }

    private function normalizePath(?string $path): string
    {
        return trim((string) $path);
    }
}

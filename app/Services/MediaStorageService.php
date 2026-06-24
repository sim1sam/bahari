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

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $this->publicUrl($path);
    }

    public function storeFromDataUri(string $dataUri, string $directory, ?string $current = null, string $field = 'image'): string
    {
        if (! preg_match('#^data:image/(\w+);base64,(.+)$#s', $dataUri, $matches)) {
            throw ValidationException::withMessages([
                $field => 'Invalid base64 image data.',
            ]);
        }

        $extension = strtolower($matches[1]);
        $extension = $extension === 'jpeg' ? 'jpg' : $extension;
        $extension = in_array($extension, ['jpg', 'png', 'webp', 'gif', 'svg'], true) ? $extension : 'jpg';

        $content = base64_decode(str_replace(' ', '+', $matches[2]), true);

        if ($content === false || strlen($content) < 100) {
            throw ValidationException::withMessages([
                $field => 'Invalid base64 image data.',
            ]);
        }

        Storage::disk('public')->makeDirectory($directory);
        $this->delete($current);

        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        if (! Storage::disk('public')->put($path, $content)) {
            throw ValidationException::withMessages([
                $field => 'Failed to save image to storage.',
            ]);
        }

        return $path;
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

        $this->ensurePublicDirectory($directory);
        $this->delete($current);

        $extension = $this->extensionFromUpload($file);
        $path = trim($directory, '/').'/'.Str::uuid().'.'.$extension;

        if (! Storage::disk('public')->put($path, $content)) {
            throw ValidationException::withMessages([
                $field => 'Failed to write image to storage. Run: php artisan storage:link and check folder permissions on storage/app/public.',
            ]);
        }

        return $path;
    }

    public function storeFromUrl(string $url, string $directory, ?string $current = null, string $field = 'image_url'): string
    {
        $url = trim($url);

        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'LuxeWear-API-Receiver/1.0'])
                ->withOptions(['allow_redirects' => true, 'verify' => true])
                ->get($url);
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

    private function ensurePublicDirectory(string $directory): void
    {
        $relative = trim($directory, '/');
        Storage::disk('public')->makeDirectory($relative);

        $absolute = storage_path('app/public/'.$relative);

        if (! is_dir($absolute) && ! @mkdir($absolute, 0755, true) && ! is_dir($absolute)) {
            throw ValidationException::withMessages([
                'logo' => 'Could not create storage directory. Check permissions on storage/app/public.',
            ]);
        }
    }

    private function extensionFromUpload(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $extension = match ($extension) {
            'jpeg' => 'jpg',
            'svg+xml' => 'svg',
            default => $extension,
        };

        $allowed = ['jpg', 'png', 'webp', 'gif', 'svg', 'ico'];

        return in_array($extension, $allowed, true) ? $extension : 'jpg';
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
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
        ];

        foreach ($map as $type => $ext) {
            if (str_contains($contentType, $type)) {
                return $ext;
            }
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'], true)
            ? (match ($ext) { 'jpeg' => 'jpg', default => $ext })
            : 'jpg';
    }

    private function publicUrl(string $path): string
    {
        if (! $this->storageSymlinkIsValid()) {
            return route('storage.file', ['path' => $path], false);
        }

        return '/storage/'.ltrim($path, '/');
    }

    public function storageSymlinkIsValid(): bool
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! file_exists($link) || ! is_dir($target)) {
            return false;
        }

        if (is_link($link)) {
            $resolvedLink = realpath($link);
            $resolvedTarget = realpath($target);

            return $resolvedLink && $resolvedTarget && $resolvedLink === $resolvedTarget;
        }

        return realpath($link) === realpath($target);
    }

    private function normalizePath(?string $path): string
    {
        $path = trim((string) $path);

        if (str_starts_with($path, '/storage/')) {
            $path = Str::after($path, '/storage/');
        } elseif (str_starts_with($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        return $path;
    }
}

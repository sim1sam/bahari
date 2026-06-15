<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class StorageLinkService
{
    /** @return array{link_path: string, target_path: string, exists: bool, is_link: bool, is_valid: bool, target_exists: bool} */
    public function status(): array
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        return [
            'link_path' => $link,
            'target_path' => $target,
            'exists' => file_exists($link),
            'is_link' => is_link($link),
            'is_valid' => $this->isValidLink($link, $target),
            'target_exists' => is_dir($target),
        ];
    }

    /** @return array{success: bool, message: string} */
    public function create(): array
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if ($this->isValidLink($link, $target)) {
            return [
                'success' => true,
                'message' => 'Storage link is already active.',
            ];
        }

        if (file_exists($link) && ! is_link($link)) {
            return [
                'success' => false,
                'message' => 'public/storage exists as a folder or file, not a symlink. Remove or rename it on the server first, then try again.',
            ];
        }

        if (! is_dir($target)) {
            File::makeDirectory($target, 0755, true);
        }

        if (is_link($link)) {
            @unlink($link);
        }

        try {
            Artisan::call('storage:link', ['--force' => true]);

            if ($this->isValidLink($link, $target)) {
                return [
                    'success' => true,
                    'message' => 'Storage link created successfully.',
                ];
            }
        } catch (\Throwable) {
            // Fall through to manual symlink attempt.
        }

        try {
            if (@symlink($target, $link) && $this->isValidLink($link, $target)) {
                return [
                    'success' => true,
                    'message' => 'Storage link created successfully.',
                ];
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Could not create storage link: '.$e->getMessage(),
            ];
        }

        return [
            'success' => false,
            'message' => 'Could not create storage link. Check that the web server user can create symlinks in the public folder.',
        ];
    }

    private function isValidLink(string $link, string $target): bool
    {
        if (! is_link($link) || ! is_dir($target)) {
            return false;
        }

        $resolvedLink = realpath($link);
        $resolvedTarget = realpath($target);

        return $resolvedLink && $resolvedTarget && $resolvedLink === $resolvedTarget;
    }
}

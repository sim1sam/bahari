<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class StorageLinkService
{
    /** @return array{link_path: string, target_path: string, exists: bool, is_link: bool, is_valid: bool, target_exists: bool, blocking_path: bool} */
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
            'blocking_path' => file_exists($link) && ! is_link($link),
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

        if (! is_dir($target)) {
            File::makeDirectory($target, 0755, true);
        }

        $relocatedMessage = null;

        if (file_exists($link) && ! is_link($link)) {
            $relocated = $this->relocateBlockingPath($link, $target);

            if (! $relocated['success']) {
                return $relocated;
            }

            $relocatedMessage = $relocated['message'];
        }

        if (is_link($link)) {
            @unlink($link);
        }

        try {
            Artisan::call('storage:link', ['--force' => true]);

            if ($this->isValidLink($link, $target)) {
                return [
                    'success' => true,
                    'message' => $this->buildSuccessMessage('Storage link created successfully.', $relocatedMessage),
                ];
            }
        } catch (\Throwable) {
            // Fall through to manual symlink attempt.
        }

        try {
            if ($this->createSymlink($target, $link) && $this->isValidLink($link, $target)) {
                return [
                    'success' => true,
                    'message' => $this->buildSuccessMessage('Storage link created successfully.', $relocatedMessage),
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
            'message' => 'Could not create storage link. Your host may block symlinks — images will still work via /media/ fallback.',
        ];
    }

    /** @return array{success: bool, message: string} */
    private function relocateBlockingPath(string $link, string $target): array
    {
        $backup = public_path('storage_backup_'.date('Ymd_His'));

        try {
            if (is_dir($link)) {
                File::moveDirectory($link, $backup);
                $this->mergeDirectoryInto($backup, $target);

                return [
                    'success' => true,
                    'message' => 'Renamed old public/storage folder to '.basename($backup).' and copied its files into storage/app/public.',
                ];
            }

            File::move($link, $backup);

            return [
                'success' => true,
                'message' => 'Renamed old public/storage file to '.basename($backup).'.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Could not rename public/storage automatically: '.$e->getMessage(),
            ];
        }
    }

    private function mergeDirectoryInto(string $source, string $destination): void
    {
        if (! is_dir($source)) {
            return;
        }

        if (! is_dir($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        /** @var SplFileInfo $file */
        foreach (File::allFiles($source) as $file) {
            $relative = $file->getRelativePathname();
            $dest = $destination.DIRECTORY_SEPARATOR.$relative;
            $destDir = dirname($dest);

            if (! is_dir($destDir)) {
                File::makeDirectory($destDir, 0755, true);
            }

            if (! file_exists($dest)) {
                File::copy($file->getPathname(), $dest);
            }
        }
    }

    private function createSymlink(string $target, string $link): bool
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $command = 'mklink /J '.escapeshellarg($link).' '.escapeshellarg($target);
            exec($command, $output, $code);

            if ($code === 0) {
                return true;
            }
        }

        return @symlink($target, $link);
    }

    private function buildSuccessMessage(string $base, ?string $relocatedMessage): string
    {
        if ($relocatedMessage) {
            return $base.' '.$relocatedMessage;
        }

        return $base;
    }

    private function isValidLink(string $link, string $target): bool
    {
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
}

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
        $valid = $this->isValidLink($link, $target);

        return [
            'link_path' => $link,
            'target_path' => $target,
            'exists' => file_exists($link),
            // Windows junctions are not always reported by is_link().
            'is_link' => is_link($link) || $valid,
            'is_valid' => $valid,
            'target_exists' => is_dir($target),
            'blocking_path' => file_exists($link) && ! $valid,
        ];
    }

    /** @return array{success: bool, message: string} */
    public function create(): array
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! is_dir($target)) {
            File::makeDirectory($target, 0755, true);
        }

        if ($this->isValidLink($link, $target)) {
            return [
                'success' => true,
                'message' => 'Storage link is already active.',
            ];
        }

        $relocatedMessage = null;

        // Only move aside paths that do NOT already resolve to the target.
        if (file_exists($link) && ! $this->isValidLink($link, $target)) {
            if (is_link($link)) {
                @\unlink($link);
            } else {
                $relocated = $this->relocateBlockingPath($link, $target);

                if (! $relocated['success']) {
                    return $relocated;
                }

                $relocatedMessage = $relocated['message'];
            }
        }

        // Remove broken symlink leftovers.
        if (is_link($link) && ! $this->isValidLink($link, $target)) {
            @\unlink($link);
        }

        $attempts = [];

        try {
            Artisan::call('storage:link', ['--force' => true]);

            if ($this->isValidLink($link, $target)) {
                return [
                    'success' => true,
                    'message' => $this->buildSuccessMessage('Storage link created successfully.', $relocatedMessage),
                ];
            }

            $attempts[] = 'artisan storage:link';
        } catch (\Throwable $e) {
            $attempts[] = 'artisan: '.$e->getMessage();
        }

        try {
            if ($this->createSymlink($target, $link) && $this->isValidLink($link, $target)) {
                return [
                    'success' => true,
                    'message' => $this->buildSuccessMessage('Storage link created successfully.', $relocatedMessage),
                ];
            }

            $attempts[] = 'manual symlink/junction';
        } catch (\Throwable $e) {
            $attempts[] = 'manual: '.$e->getMessage();
        }

        // Final check — another process may have created it, or junction already existed.
        if ($this->isValidLink($link, $target)) {
            return [
                'success' => true,
                'message' => $this->buildSuccessMessage('Storage link is active.', $relocatedMessage),
            ];
        }

        return [
            'success' => false,
            'message' => 'Could not create storage link ('.implode('; ', $attempts).'). '
                .'Images still work via /media/ fallback. On Windows run as Admin: mklink /J "'. $link.'" "'.$target.'". '
                .'On Linux: ln -s "'.$target.'" "'.$link.'"',
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
        if (file_exists($link) || is_link($link)) {
            return $this->isValidLink($link, $target);
        }

        // Windows: directory junction usually works without admin rights.
        if (DIRECTORY_SEPARATOR === '\\') {
            $command = 'cmd /c mklink /J '.escapeshellarg($link).' '.escapeshellarg($target);
            @exec($command, $output, $code);

            if ($this->isValidLink($link, $target)) {
                return true;
            }
        } else {
            // Linux/macOS shell fallback when PHP symlink() is disabled.
            $command = 'ln -s '.escapeshellarg($target).' '.escapeshellarg($link);
            @exec($command, $output, $code);

            if ($this->isValidLink($link, $target)) {
                return true;
            }
        }

        if (! \function_exists('symlink')) {
            return false;
        }

        $created = @\symlink($target, $link);

        return $created && $this->isValidLink($link, $target);
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

        $resolvedTarget = realpath($target);

        if (! $resolvedTarget) {
            return false;
        }

        // Symlink (Unix / Windows symlink)
        if (is_link($link)) {
            $resolvedLink = realpath($link);

            return $resolvedLink !== false && $resolvedLink === $resolvedTarget;
        }

        // Windows junction / directory mount: is_link() is often false, but realpath matches.
        if (is_dir($link)) {
            $resolvedLink = realpath($link);

            return $resolvedLink !== false && $resolvedLink === $resolvedTarget;
        }

        return false;
    }
}

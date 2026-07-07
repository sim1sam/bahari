<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class FrontendBuildService
{
    /** @return array{node_available: bool, npm_available: bool, node_version: ?string, npm_version: ?string, node_modules: bool, build_dir: bool, assets_dir: bool, manifest_exists: bool, css_file: ?string, js_file: ?string, css_exists: bool, js_exists: bool, assets_count: int, last_built_at: ?string, project_path: string, is_broken: bool} */
    public function status(): array
    {
        $manifest = $this->readManifest();
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        $manifestPath = public_path('build/manifest.json');
        $assetsDir = public_path('build/assets');
        $cssExists = $cssFile ? is_file(public_path('build/'.$cssFile)) : false;
        $jsExists = $jsFile ? is_file(public_path('build/'.$jsFile)) : false;

        return [
            'node_available' => $this->commandWorks($this->nodeBinary(), ['--version']),
            'npm_available' => $this->commandWorks($this->npmBinary(), ['--version']),
            'node_version' => $this->commandVersion($this->nodeBinary(), ['--version']),
            'npm_version' => $this->commandVersion($this->npmBinary(), ['--version']),
            'node_modules' => is_dir(base_path('node_modules')),
            'build_dir' => is_dir(public_path('build')),
            'assets_dir' => is_dir($assetsDir),
            'manifest_exists' => is_file($manifestPath),
            'css_file' => $cssFile,
            'js_file' => $jsFile,
            'css_exists' => $cssExists,
            'js_exists' => $jsExists,
            'assets_count' => is_dir($assetsDir) ? count(File::files($assetsDir)) : 0,
            'last_built_at' => is_file($manifestPath)
                ? date('Y-m-d H:i:s', (int) filemtime($manifestPath))
                : null,
            'project_path' => base_path(),
            'is_broken' => is_file($manifestPath) && (! $cssExists || ! $jsExists),
        ];
    }

    /** @return array{success: bool, message: string, output: string} */
    public function run(): array
    {
        if (! is_dir(base_path('node_modules'))) {
            return [
                'success' => false,
                'message' => 'node_modules folder is missing. Run npm install on the server first.',
                'output' => '',
            ];
        }

        if (! $this->commandWorks($this->npmBinary(), ['--version'])) {
            return [
                'success' => false,
                'message' => 'npm is not available to PHP. Install Node.js and add npm to the web server PATH, or set NPM_BINARY in .env.',
                'output' => '',
            ];
        }

        try {
            $process = $this->makeNpmProcess(['run', 'build:deploy']);
            $process->run();

            $output = trim($process->getOutput()."\n".$process->getErrorOutput());
            $status = $this->status();

            if (! $process->isSuccessful()) {
                return [
                    'success' => false,
                    'message' => 'npm build failed. Check the output below.',
                    'output' => $output,
                ];
            }

            if (! $status['css_exists'] || ! $status['js_exists']) {
                return [
                    'success' => false,
                    'message' => 'Build command finished but CSS/JS files are still missing in public/build/assets/.',
                    'output' => $output,
                ];
            }

            return [
                'success' => true,
                'message' => 'Frontend build completed successfully. CSS and JS are ready in public/build/.',
                'output' => $output,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Build failed: '.$e->getMessage(),
                'output' => '',
            ];
        }
    }

    /** @return array{success: bool, message: string, output: string} */
    public function uploadDeployZip(UploadedFile $file): array
    {
        if (! class_exists(ZipArchive::class)) {
            return [
                'success' => false,
                'message' => 'PHP Zip extension is required to upload build files.',
                'output' => '',
            ];
        }

        $zip = new ZipArchive;
        $opened = $zip->open($file->getRealPath());

        if ($opened !== true) {
            return [
                'success' => false,
                'message' => 'Could not open the uploaded zip file.',
                'output' => '',
            ];
        }

        $hasManifest = false;
        $hasAssets = false;

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entry = (string) $zip->getNameIndex($index);

            if (str_contains($entry, '..')) {
                $zip->close();

                return [
                    'success' => false,
                    'message' => 'Unsafe zip entry detected. Use deploy-build.zip from npm run deploy.',
                    'output' => '',
                ];
            }

            if (preg_match('#(^|/)manifest\.json$#', $entry)) {
                $hasManifest = true;
            }

            if (preg_match('#(^|/)assets/#', $entry)) {
                $hasAssets = true;
            }
        }

        if (! $hasManifest || ! $hasAssets) {
            $zip->close();

            return [
                'success' => false,
                'message' => 'Invalid build zip. Upload deploy-build.zip created by npm run deploy on your computer.',
                'output' => '',
            ];
        }

        $buildDir = public_path('build');

        try {
            if (is_dir($buildDir)) {
                File::cleanDirectory($buildDir);
            } else {
                File::ensureDirectoryExists($buildDir);
            }

            if (! $zip->extractTo($buildDir)) {
                $zip->close();

                return [
                    'success' => false,
                    'message' => 'Could not extract build files into public/build/. Check folder permissions.',
                    'output' => '',
                ];
            }

            $zip->close();
            $status = $this->status();

            if (! $status['css_exists'] || ! $status['js_exists']) {
                return [
                    'success' => false,
                    'message' => 'Zip extracted but CSS/JS files are still missing. Re-create deploy-build.zip locally with npm run deploy.',
                    'output' => 'Assets found: '.$status['assets_count'],
                ];
            }

            return [
                'success' => true,
                'message' => 'Build files uploaded successfully. Hard refresh the storefront to see the design.',
                'output' => trim(
                    "CSS: {$status['css_file']}\n".
                    "JS: {$status['js_file']}\n".
                    "Assets: {$status['assets_count']} files"
                ),
            ];
        } catch (\Throwable $e) {
            $zip->close();

            return [
                'success' => false,
                'message' => 'Upload failed: '.$e->getMessage(),
                'output' => '',
            ];
        }
    }

    /** @return array<string, mixed> */
    private function readManifest(): array
    {
        $manifestPath = public_path('build/manifest.json');

        if (! is_file($manifestPath)) {
            return [];
        }

        $decoded = json_decode((string) file_get_contents($manifestPath), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function nodeBinary(): string
    {
        return (string) env('NODE_BINARY', 'node');
    }

    private function npmBinary(): string
    {
        return (string) env('NPM_BINARY', 'npm');
    }

    /** @param  array<int, string>  $arguments */
    private function makeNpmProcess(array $arguments): Process
    {
        $command = array_merge([$this->npmBinary()], $arguments);
        $process = new Process($command, base_path());
        $process->setTimeout(300);

        if (PHP_OS_FAMILY === 'Windows') {
            $process->setOptions(['create_new_console' => true]);
        }

        return $process;
    }

    /** @param  array<int, string>  $arguments */
    private function commandWorks(string $binary, array $arguments): bool
    {
        return $this->commandVersion($binary, $arguments) !== null;
    }

    /** @param  array<int, string>  $arguments */
    private function commandVersion(string $binary, array $arguments): ?string
    {
        try {
            $process = new Process(array_merge([$binary], $arguments), base_path());
            $process->setTimeout(15);
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            $version = trim($process->getOutput() ?: $process->getErrorOutput());

            return $version !== '' ? $version : null;
        } catch (\Throwable) {
            return null;
        }
    }
}

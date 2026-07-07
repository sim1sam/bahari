<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class FrontendBuildService
{
    /** @return array{node_available: bool, npm_available: bool, node_version: ?string, npm_version: ?string, node_modules: bool, build_dir: bool, manifest_exists: bool, css_file: ?string, js_file: ?string, css_exists: bool, js_exists: bool, assets_count: int, last_built_at: ?string, project_path: string} */
    public function status(): array
    {
        $manifest = $this->readManifest();
        $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
        $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        $manifestPath = public_path('build/manifest.json');
        $assetsDir = public_path('build/assets');

        return [
            'node_available' => $this->commandWorks($this->nodeBinary(), ['--version']),
            'npm_available' => $this->commandWorks($this->npmBinary(), ['--version']),
            'node_version' => $this->commandVersion($this->nodeBinary(), ['--version']),
            'npm_version' => $this->commandVersion($this->npmBinary(), ['--version']),
            'node_modules' => is_dir(base_path('node_modules')),
            'build_dir' => is_dir(public_path('build')),
            'manifest_exists' => is_file($manifestPath),
            'css_file' => $cssFile,
            'js_file' => $jsFile,
            'css_exists' => $cssFile ? is_file(public_path('build/'.$cssFile)) : false,
            'js_exists' => $jsFile ? is_file(public_path('build/'.$jsFile)) : false,
            'assets_count' => is_dir($assetsDir) ? count(File::files($assetsDir)) : 0,
            'last_built_at' => is_file($manifestPath)
                ? date('Y-m-d H:i:s', (int) filemtime($manifestPath))
                : null,
            'project_path' => base_path(),
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

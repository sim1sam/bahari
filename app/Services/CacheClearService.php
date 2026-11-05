<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

class CacheClearService
{
    /**
     * @return array{success: bool, message: string, output: string, commands: array<int, string>}
     */
    public function run(): array
    {
        $commands = [
            'cache:clear',
            'view:clear',
            'route:clear',
            'config:clear',
        ];

        $chunks = [];

        try {
            foreach ($commands as $command) {
                Artisan::call($command);
                $out = trim(Artisan::output());
                $chunks[] = '$ php artisan '.$command.($out !== '' ? "\n".$out : "\nCleared.");
            }

            // Bust homepage/footer link caches used by the storefront.
            app(HomepageContentService::class)->clearCache();
            $chunks[] = 'Homepage / footer content cache cleared.';

            return [
                'success' => true,
                'message' => 'Cache cleared successfully (cache, views, routes, config).',
                'output' => implode("\n\n", $chunks),
                'commands' => $commands,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Cache clear failed: '.$e->getMessage(),
                'output' => implode("\n\n", $chunks)."\n\n".$e->getMessage(),
                'commands' => $commands,
            ];
        }
    }
}

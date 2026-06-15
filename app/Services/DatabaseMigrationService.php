<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseMigrationService
{
    /** @return array{database: string, total: int, ran: int, pending_count: int, pending: array<int, string>} */
    public function status(): array
    {
        $files = collect(File::glob(database_path('migrations/*.php')))
            ->map(fn (string $path) => pathinfo($path, PATHINFO_FILENAME))
            ->sort()
            ->values();

        $ran = DB::table('migrations')->pluck('migration');
        $pending = $files->diff($ran)->values();

        return [
            'database' => (string) config('database.default'),
            'total' => $files->count(),
            'ran' => $ran->count(),
            'pending_count' => $pending->count(),
            'pending' => $pending->all(),
        ];
    }

    /** @return array{success: bool, message: string, output: string} */
    public function run(): array
    {
        $pendingBefore = $this->status()['pending_count'];

        if ($pendingBefore === 0) {
            return [
                'success' => true,
                'message' => 'Database is already up to date. No pending migrations.',
                'output' => '',
            ];
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            $pendingAfter = $this->status()['pending_count'];

            if ($pendingAfter > 0) {
                return [
                    'success' => false,
                    'message' => 'Some migrations did not complete. '.$pendingAfter.' still pending.',
                    'output' => $output,
                ];
            }

            return [
                'success' => true,
                'message' => 'Database migrations completed successfully.',
                'output' => $output,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Migration failed: '.$e->getMessage(),
                'output' => trim(Artisan::output()),
            ];
        }
    }
}

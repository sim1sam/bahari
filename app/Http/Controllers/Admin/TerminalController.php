<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseMigrationService;
use App\Services\FrontendBuildService;
use App\Services\StorageLinkService;
use App\Support\AdminFeatures;
use Illuminate\View\View;

class TerminalController extends Controller
{
    public function index(
        DatabaseMigrationService $migrations,
        FrontendBuildService $frontendBuild,
        StorageLinkService $storageLink,
    ): View {
        $user = auth()->user();
        $features = AdminFeatures::all();

        $tools = [];

        if ($user->canAccessAdminFeature('database_migration')) {
            $migrationStatus = $migrations->status();
            $tools[] = [
                'key' => 'database_migration',
                'label' => $features['database_migration']['label'],
                'icon' => $features['database_migration']['icon'],
                'route' => $features['database_migration']['route'],
                'description' => 'Run pending database migrations after uploading new code.',
                'status' => $migrationStatus['pending_count'] === 0 ? 'ready' : 'action',
                'status_label' => $migrationStatus['pending_count'] === 0
                    ? 'Up to date'
                    : $migrationStatus['pending_count'].' pending',
            ];
        }

        if ($user->canAccessAdminFeature('npm_build')) {
            $buildStatus = $frontendBuild->status();
            $tools[] = [
                'key' => 'npm_build',
                'label' => $features['npm_build']['label'],
                'icon' => $features['npm_build']['icon'],
                'route' => $features['npm_build']['route'],
                'description' => 'Build storefront CSS and JavaScript with npm on this server.',
                'status' => ($buildStatus['css_exists'] && $buildStatus['js_exists']) ? 'ready' : 'action',
                'status_label' => ($buildStatus['css_exists'] && $buildStatus['js_exists']) ? 'Built' : 'Build needed',
            ];
        }

        if ($user->canAccessAdminFeature('storage_link')) {
            $linkStatus = $storageLink->status();
            $tools[] = [
                'key' => 'storage_link',
                'label' => $features['storage_link']['label'],
                'icon' => $features['storage_link']['icon'],
                'route' => $features['storage_link']['route'],
                'description' => 'Create the public storage link for product images and uploads.',
                'status' => $linkStatus['is_valid'] ? 'ready' : 'action',
                'status_label' => $linkStatus['is_valid'] ? 'Active' : 'Missing',
            ];
        }

        return view('admin.terminal.index', [
            'tools' => $tools,
        ]);
    }
}

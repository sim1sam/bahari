<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseMigrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MigrationController extends Controller
{
    public function index(DatabaseMigrationService $migrations): View
    {
        return view('admin.migration.index', [
            'status' => $migrations->status(),
            'lastOutput' => session('migration_output'),
        ]);
    }

    public function store(DatabaseMigrationService $migrations): RedirectResponse
    {
        $result = $migrations->run();

        return back()
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('migration_output', $result['output']);
    }
}

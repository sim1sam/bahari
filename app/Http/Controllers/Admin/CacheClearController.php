<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CacheClearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CacheClearController extends Controller
{
    public function index(): View
    {
        return view('admin.cache-clear.index', [
            'lastOutput' => session('cache_clear_output'),
        ]);
    }

    public function store(CacheClearService $cacheClear): RedirectResponse
    {
        $result = $cacheClear->run();

        return back()
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('cache_clear_output', $result['output']);
    }
}

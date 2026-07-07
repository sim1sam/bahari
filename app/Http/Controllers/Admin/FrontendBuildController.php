<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FrontendBuildService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontendBuildController extends Controller
{
    public function index(FrontendBuildService $build): View
    {
        return view('admin.frontend-build.index', [
            'status' => $build->status(),
            'lastOutput' => session('frontend_build_output'),
        ]);
    }

    public function store(FrontendBuildService $build): RedirectResponse
    {
        $result = $build->run();

        return back()
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('frontend_build_output', $result['output']);
    }

    public function upload(Request $request, FrontendBuildService $build): RedirectResponse
    {
        $request->validate([
            'build_zip' => ['required', 'file', 'mimes:zip', 'max:51200'],
        ]);

        $result = $build->uploadDeployZip($request->file('build_zip'));

        return back()
            ->with($result['success'] ? 'success' : 'error', $result['message'])
            ->with('frontend_build_output', $result['output']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StorageLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StorageLinkController extends Controller
{
    public function index(StorageLinkService $storageLink): View
    {
        return view('admin.storage-link.index', [
            'status' => $storageLink->status(),
        ]);
    }

    public function store(StorageLinkService $storageLink): RedirectResponse
    {
        $result = $storageLink->create();

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}

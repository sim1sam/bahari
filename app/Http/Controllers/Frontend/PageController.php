<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.content.show', compact('page'));
    }
}

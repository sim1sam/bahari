<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\HomepageContentService;

class HomeController extends Controller
{
    public function __construct(private HomepageContentService $homepage) {}

    public function index()
    {
        return view('pages.home.index', [
            'heroSlides' => $this->homepage->sliders(),
            'banners' => $this->homepage->banners(),
            'features' => $this->homepage->features(),
        ]);
    }
}

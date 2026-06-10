<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use App\Models\HomeBanner;
use App\Models\HomeFeature;
use App\Models\HomeSlider;
use Illuminate\View\View;

class HomepageController extends Controller
{
    public function index(): View
    {
        return view('admin.homepage.index', [
            'sliderCount' => HomeSlider::count(),
            'bannerCount' => HomeBanner::count(),
            'featureCount' => HomeFeature::count(),
            'footerLinkCount' => FooterLink::count(),
        ]);
    }
}

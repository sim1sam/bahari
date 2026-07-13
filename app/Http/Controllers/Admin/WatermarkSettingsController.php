<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\ProductLogoService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WatermarkSettingsController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function edit(): View
    {
        return view('admin.settings.watermark', [
            'logoUrl' => $this->settings->apiLogoUrl(),
            'logoScale' => Schema::hasColumn((new SiteSetting)->getTable(), 'api_logo_scale')
                ? (SiteSetting::current()->api_logo_scale ?: 28)
                : 28,
        ]);
    }

    public function uploadLogo(Request $request, ProductLogoService $logoService): RedirectResponse
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $logoService->storeSiteLogo($request->file('logo'));
        $this->settings->clearCache();

        return back()->with('success', 'Watermark logo uploaded.');
    }

    public function updateLogoScale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_logo_scale' => 'required|integer|min:10|max:50',
        ]);

        $settings = SiteSetting::current();
        $settings->api_logo_scale = (int) $validated['api_logo_scale'];
        $settings->save();
        $this->settings->clearCache();

        return back()->with('success', 'Watermark size updated to '.$settings->api_logo_scale.'% of image width.');
    }
}

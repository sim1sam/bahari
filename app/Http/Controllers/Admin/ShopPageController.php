<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopPageBrandSchedule;
use App\Models\ShopPageSetting;
use App\Services\ShopPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopPageController extends Controller
{
    public function __construct(private ShopPageService $shop) {}

    public function edit(): View
    {
        $settings = ShopPageSetting::current();

        return view('admin.shop-page.edit', [
            'settings' => $settings,
            'activeBrandCount' => ShopPageBrandSchedule::currentlyActive()->count(),
            'selectedProductCount' => count($settings->featuredProductIds()),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_enabled' => 'boolean',
            'hero_title' => 'required|string|max:200',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_cta_label' => 'nullable|string|max:100',
            'section_title' => 'nullable|string|max:200',
            'section_subtitle' => 'nullable|string|max:500',
        ]);

        ShopPageSetting::current()->update([
            'is_enabled' => $request->boolean('is_enabled'),
            'hero_title' => $validated['hero_title'],
            'hero_subtitle' => $validated['hero_subtitle'] ?? null,
            'hero_cta_label' => $validated['hero_cta_label'] ?? null,
            'section_title' => $validated['section_title'] ?? null,
            'section_subtitle' => $validated['section_subtitle'] ?? null,
        ]);

        $this->shop->clearCache();

        return redirect()
            ->route('admin.shop-page.edit')
            ->with('success', 'Shop page settings updated.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingSettingsController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function edit(): View
    {
        return view('admin.shipping.edit', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_fee_inside_dhaka' => 'required|numeric|min:0',
            'shipping_fee_outside_dhaka' => 'required|numeric|min:0',
            'free_shipping_threshold' => 'required|numeric|min:0',
        ]);

        $settings = SiteSetting::current();
        $settings->update($validated);
        $this->settings->clearCache();

        return redirect()
            ->route('admin.shipping.edit')
            ->with('success', 'Shipping settings updated.');
    }
}

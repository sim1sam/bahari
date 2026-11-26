<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CostCalculationSetting;
use App\Models\ShippingCharge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShippingChargeController extends Controller
{
    public function index(): View
    {
        return view('admin.shipping-charges.index', [
            'shippingCharges' => ShippingCharge::query()
                ->orderBy('sort_order')
                ->orderBy('item_name')
                ->paginate(50),
            'setting' => CostCalculationSetting::getSetting(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'unit' => 'required|in:KG,PC',
            'retail_rate' => 'required|numeric|min:0',
            'show_in_cost_calculation' => 'nullable',
        ]);

        ShippingCharge::create([
            'item_name' => $validated['item_name'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'unit' => $validated['unit'],
            'retail_rate' => $validated['retail_rate'],
            'show_in_cost_calculation' => $request->boolean('show_in_cost_calculation', true),
        ]);

        return redirect()
            ->route('admin.shipping-charges.index')
            ->with('success', 'Shipping category created.');
    }

    public function update(Request $request, ShippingCharge $shippingCharge): RedirectResponse
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'unit' => 'required|in:KG,PC',
            'retail_rate' => 'required|numeric|min:0',
            'show_in_cost_calculation' => 'nullable',
        ]);

        $shippingCharge->update([
            'item_name' => $validated['item_name'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'unit' => $validated['unit'],
            'retail_rate' => $validated['retail_rate'],
            'show_in_cost_calculation' => $request->boolean('show_in_cost_calculation'),
        ]);

        return redirect()
            ->route('admin.shipping-charges.index')
            ->with('success', 'Shipping category updated.');
    }

    public function destroy(ShippingCharge $shippingCharge): RedirectResponse
    {
        $shippingCharge->delete();

        return redirect()
            ->route('admin.shipping-charges.index')
            ->with('success', 'Shipping category deleted.');
    }

    public function updateConversionRate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'conversion_rate' => 'required|numeric|min:0.01|max:100',
        ]);

        $setting = CostCalculationSetting::getSetting();
        $setting->update([
            'conversion_rate' => round((float) $validated['conversion_rate'], 2),
        ]);

        return redirect()
            ->route('admin.shipping-charges.index')
            ->with('success', 'Conversion rate updated to '.$setting->conversion_rate.'.');
    }
}

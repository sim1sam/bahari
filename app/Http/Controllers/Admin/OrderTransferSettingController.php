<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderTransferSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTransferSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.orders.transfer-settings', [
            'setting' => OrderTransferSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:150',
            'domain' => 'nullable|url|max:255',
            'endpoint_path' => 'required|string|max:255',
            'api_key' => 'nullable|string|max:255',
            'access_token' => 'nullable|string|max:2000',
        ]);

        $validated['endpoint_path'] = '/'.ltrim($validated['endpoint_path'], '/');
        $validated['is_active'] = $request->boolean('is_active');

        OrderTransferSetting::current()->update($validated);

        return redirect()
            ->route('admin.orders.transfer-settings.edit')
            ->with('success', 'Order transfer setting updated.');
    }
}

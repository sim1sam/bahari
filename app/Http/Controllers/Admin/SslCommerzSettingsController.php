<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SslCommerzSettingsController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function edit(): View
    {
        return view('admin.ssl-settings.edit', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = SiteSetting::current();

        $validated = $request->validate([
            'sslcommerz_enabled' => 'boolean',
            'sslcommerz_sandbox' => 'boolean',
            'sslcommerz_store_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf($request->boolean('sslcommerz_enabled') && Schema::hasColumn('site_settings', 'sslcommerz_store_id')),
            ],
            'sslcommerz_store_password' => 'nullable|string|max:100',
        ]);

        $data = [
            'sslcommerz_enabled' => $request->boolean('sslcommerz_enabled'),
            'sslcommerz_sandbox' => $request->boolean('sslcommerz_sandbox'),
            'sslcommerz_store_id' => $validated['sslcommerz_store_id'] ?? null,
        ];

        if ($request->boolean('sslcommerz_enabled') && Schema::hasColumn('site_settings', 'sslcommerz_store_password')) {
            $hasPassword = filled($settings->sslcommerz_store_password)
                || filled($validated['sslcommerz_store_password'] ?? null);

            if (! $hasPassword) {
                return back()
                    ->withInput()
                    ->withErrors(['sslcommerz_store_password' => 'Store password is required when SSLCommerz is enabled.']);
            }
        }

        if (filled($validated['sslcommerz_store_password'] ?? null) && Schema::hasColumn('site_settings', 'sslcommerz_store_password')) {
            $data['sslcommerz_store_password'] = $validated['sslcommerz_store_password'];
        }

        $settings->update($data);
        $this->settings->clearCache();

        return redirect()
            ->route('admin.ssl-settings.edit')
            ->with('success', 'SSL settings updated.');
    }
}

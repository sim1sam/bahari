<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSource;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiSettingsController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function index(): View
    {
        $siteSettings = SiteSetting::current();

        return view('admin.api-settings.index', [
            'sources' => ApiSource::latest()->get(),
            'receiveUrl' => $this->settings->apiReceiveUrl(),
            'webhookBaseUrl' => $siteSettings->api_webhook_url,
        ]);
    }

    public function updateWebhook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_webhook_url' => 'nullable|url|max:500',
        ]);

        $settings = SiteSetting::current();
        $settings->api_webhook_url = $validated['api_webhook_url'] ?: null;
        $settings->save();
        $this->settings->clearCache();

        return back()->with('success', 'Public webhook URL saved.');
    }

    public function storeSource(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'api_key' => 'required|string|max:64',
            'api_token' => 'required|string|max:128',
            'base_url' => 'nullable|url|max:500',
        ]);

        ApiSource::create([
            'name' => $validated['name'],
            'base_url' => $validated['base_url'] ?? null,
            'api_key' => $validated['api_key'],
            'api_token' => $validated['api_token'],
            'is_active' => true,
        ]);

        return back()->with('success', 'API key & token saved for '.$validated['name'].'.');
    }

    public function generateSource(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $credentials = ApiSource::generateCredentials();

        ApiSource::create([
            'name' => $validated['name'],
            'api_key' => $credentials['api_key'],
            'api_token' => $credentials['api_token'],
            'is_active' => true,
        ]);

        return back()->with([
            'success' => 'API credentials generated for "'.$validated['name'].'".',
            'generated_credentials' => $credentials,
        ]);
    }

    public function updateSource(Request $request, ApiSource $source): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'base_url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $source->update([
            'name' => $validated['name'],
            'base_url' => $validated['base_url'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'API source updated.');
    }

    public function destroySource(ApiSource $source): RedirectResponse
    {
        $source->delete();

        return back()->with('success', 'API source removed.');
    }
}

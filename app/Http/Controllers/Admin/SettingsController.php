<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => SiteSetting::current(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $settings = SiteSetting::current();

        $validated = $request->validate([
            'site_name' => 'required|string|max:100',
            'tagline' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'logo_url' => 'nullable|url|max:500',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,ico,svg,webp|max:1024',
            'favicon_url' => 'nullable|url|max:500',
            'meta_title' => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:150',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
            'footer_description' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:30',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'remove_logo' => 'boolean',
            'remove_favicon' => 'boolean',
        ]);

        $data = collect($validated)->except([
            'logo', 'favicon', 'logo_url', 'favicon_url', 'remove_logo', 'remove_favicon',
        ])->all();

        if ($request->boolean('remove_logo')) {
            $this->deleteStoredFile($settings->logo);
            $data['logo'] = null;
        } elseif ($request->hasFile('logo')) {
            $this->deleteStoredFile($settings->logo);
            $data['logo'] = $request->file('logo')->store('settings', 'public');
        } elseif (! empty($validated['logo_url'])) {
            $data['logo'] = $validated['logo_url'];
        }

        if ($request->boolean('remove_favicon')) {
            $this->deleteStoredFile($settings->favicon);
            $data['favicon'] = null;
        } elseif ($request->hasFile('favicon')) {
            $this->deleteStoredFile($settings->favicon);
            $data['favicon'] = $request->file('favicon')->store('settings', 'public');
        } elseif (! empty($validated['favicon_url'])) {
            $data['favicon'] = $validated['favicon_url'];
        }

        $settings->update($data);
        $this->settings->clearCache();

        return redirect()->route('admin.settings.edit')->with('success', 'Site settings updated.');
    }

    private function deleteStoredFile(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}

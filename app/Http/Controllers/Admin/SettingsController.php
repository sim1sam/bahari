<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\MediaStorageService;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function __construct(
        private SiteSettingsService $settings,
        private MediaStorageService $media,
    ) {}

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
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'logo_url' => 'nullable|url|max:500',
            'favicon' => 'nullable|file|mimes:png,jpg,jpeg,ico,svg,webp|max:1024',
            'favicon_url' => 'nullable|url|max:500',
            'meta_title' => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'og_title' => 'nullable|string|max:150',
            'og_description' => 'nullable|string|max:500',
            'og_image' => 'nullable|url|max:500',
            'gtm_enabled' => 'boolean',
            'gtm_container_id' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^GTM-[A-Z0-9]+$/i',
                Rule::requiredIf($request->boolean('gtm_enabled') && Schema::hasColumn('site_settings', 'gtm_container_id')),
            ],
            'sslcommerz_enabled' => 'boolean',
            'sslcommerz_sandbox' => 'boolean',
            'sslcommerz_store_id' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf($request->boolean('sslcommerz_enabled') && Schema::hasColumn('site_settings', 'sslcommerz_store_id')),
            ],
            'sslcommerz_store_password' => 'nullable|string|max:100',
            'footer_description' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email|max:150',
            'contact_phone' => 'nullable|string|max:30',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'top_bar_text' => 'nullable|string|max:255',
            'top_bar_text_mobile' => 'nullable|string|max:255',
            'top_bar_bg_color' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'top_bar_text_color' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'top_bar_link_color' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'newsletter_title' => 'nullable|string|max:100',
            'newsletter_text' => 'nullable|string|max:255',
            'newsletter_placeholder' => 'nullable|string|max:100',
            'newsletter_button_text' => 'nullable|string|max:50',
            'newsletter_enabled' => 'boolean',
            'newsletter_success_message' => 'nullable|string|max:255',
            'footer_shop_title' => 'nullable|string|max:100',
            'footer_support_title' => 'nullable|string|max:100',
            'footer_copyright' => 'nullable|string|max:255',
            'theme_primary' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'theme_primary_dark' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'theme_footer_bg' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'theme_text' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'theme_background' => ['nullable', 'string', 'max:20', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'remove_logo' => 'boolean',
            'remove_favicon' => 'boolean',
        ]);

        $data = collect($validated)->except([
            'logo', 'favicon', 'logo_url', 'favicon_url', 'remove_logo', 'remove_favicon',
        ])->all();

        $data['newsletter_enabled'] = $request->boolean('newsletter_enabled');
        $data['gtm_enabled'] = $request->boolean('gtm_enabled');
        $data['gtm_container_id'] = filled($validated['gtm_container_id'] ?? null)
            ? strtoupper(trim($validated['gtm_container_id']))
            : null;
        $data['sslcommerz_enabled'] = $request->boolean('sslcommerz_enabled');
        $data['sslcommerz_sandbox'] = $request->boolean('sslcommerz_sandbox');

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
        } else {
            unset($data['sslcommerz_store_password']);
        }

        try {
            if ($request->boolean('remove_logo')) {
                $this->media->delete($settings->logo);
                $data['logo'] = null;
            } else {
                $logoFile = $request->file('logo');
                if ($logoFile && $logoFile->getError() !== UPLOAD_ERR_NO_FILE) {
                    $data['logo'] = $this->media->storeUpload($logoFile, 'settings', $settings->logo, 'logo');
                } elseif (! empty($validated['logo_url'])) {
                    $data['logo'] = $this->media->storeFromUrl($validated['logo_url'], 'settings', $settings->logo, 'logo_url');
                }
            }

            if ($request->boolean('remove_favicon')) {
                $this->media->delete($settings->favicon);
                $data['favicon'] = null;
            } else {
                $faviconFile = $request->file('favicon');
                if ($faviconFile && $faviconFile->getError() !== UPLOAD_ERR_NO_FILE) {
                    $data['favicon'] = $this->media->storeUpload($faviconFile, 'settings', $settings->favicon, 'favicon');
                } elseif (! empty($validated['favicon_url'])) {
                    $data['favicon'] = $this->media->storeFromUrl($validated['favicon_url'], 'settings', $settings->favicon, 'favicon_url');
                }
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Could not save logo or favicon. Check storage permissions and run: php artisan storage:link');
        }

        $data = $this->onlyExistingColumns($settings, $data);

        try {
            $settings->update($data);
        } catch (QueryException $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Could not save settings. Please run database migrations on the server: php artisan migrate --force');
        }

        $this->settings->clearCache();

        return redirect()->route('admin.settings.edit')->with('success', 'Site settings updated.');
    }

    /** @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function onlyExistingColumns(SiteSetting $settings, array $data): array
    {
        $columns = Schema::getColumnListing($settings->getTable());

        return array_intersect_key($data, array_flip($columns));
    }
}

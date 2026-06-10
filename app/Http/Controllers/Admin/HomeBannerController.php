<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesHomepageImages;
use App\Http\Controllers\Controller;
use App\Models\HomeBanner;
use App\Services\HomepageContentService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeBannerController extends Controller
{
    use HandlesHomepageImages;

    public function __construct(
        private MediaStorageService $media,
        private HomepageContentService $homepage,
    ) {}

    public function index(): View
    {
        return view('admin.homepage.banners.index', [
            'banners' => HomeBanner::orderBy('sort_order')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.homepage.banners.form', ['banner' => new HomeBanner]);
    }

    public function store(Request $request): RedirectResponse
    {
        HomeBanner::create($this->validated($request));

        return redirect()->route('admin.homepage.banners.index')->with('success', 'Banner created.');
    }

    public function edit(HomeBanner $banner): View
    {
        return view('admin.homepage.banners.form', compact('banner'));
    }

    public function update(Request $request, HomeBanner $banner): RedirectResponse
    {
        $banner->update($this->validated($request, $banner));

        return redirect()->route('admin.homepage.banners.index')->with('success', 'Banner updated.');
    }

    public function destroy(HomeBanner $banner): RedirectResponse
    {
        $this->media->delete($banner->image);
        $banner->delete();
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.banners.index')->with('success', 'Banner deleted.');
    }

    private function validated(Request $request, ?HomeBanner $banner = null): array
    {
        $data = $request->validate([
            'badge' => 'nullable|string|max:100',
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'button_text' => 'nullable|string|max:100',
            'button_href' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'image_url' => 'nullable|url|max:500',
            'remove_image' => 'boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['image'] = $this->resolveImage($request, $this->media, 'homepage/banners', $banner?->image);

        unset($data['image_url'], $data['remove_image']);
        $this->homepage->clearCache();

        return $data;
    }
}

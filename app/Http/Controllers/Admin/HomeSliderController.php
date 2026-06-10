<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesHomepageImages;
use App\Http\Controllers\Controller;
use App\Models\HomeSlider;
use App\Services\HomepageContentService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeSliderController extends Controller
{
    use HandlesHomepageImages;

    public function __construct(
        private MediaStorageService $media,
        private HomepageContentService $homepage,
    ) {}

    public function index(): View
    {
        return view('admin.homepage.sliders.index', [
            'sliders' => HomeSlider::orderBy('sort_order')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.homepage.sliders.form', ['slider' => new HomeSlider]);
    }

    public function store(Request $request): RedirectResponse
    {
        HomeSlider::create($this->validated($request));

        return redirect()->route('admin.homepage.sliders.index')->with('success', 'Slide created.');
    }

    public function edit(HomeSlider $slider): View
    {
        return view('admin.homepage.sliders.form', compact('slider'));
    }

    public function update(Request $request, HomeSlider $slider): RedirectResponse
    {
        $slider->update($this->validated($request, $slider));

        return redirect()->route('admin.homepage.sliders.index')->with('success', 'Slide updated.');
    }

    public function destroy(HomeSlider $slider): RedirectResponse
    {
        $this->media->delete($slider->image);
        $slider->delete();
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.sliders.index')->with('success', 'Slide deleted.');
    }

    private function validated(Request $request, ?HomeSlider $slider = null): array
    {
        $data = $request->validate([
            'badge' => 'nullable|string|max:100',
            'title' => 'required|string|max:200',
            'subtitle' => 'nullable|string|max:500',
            'primary_btn' => 'nullable|string|max:100',
            'primary_href' => 'nullable|string|max:500',
            'secondary_btn' => 'nullable|string|max:100',
            'secondary_href' => 'nullable|string|max:500',
            'features' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'image_url' => 'nullable|url|max:500',
            'remove_image' => 'boolean',
        ]);

        $data['features'] = array_values(array_filter(array_map('trim', explode(',', $data['features'] ?? ''))));
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['image'] = $this->resolveImage($request, $this->media, 'homepage/sliders', $slider?->image);

        unset($data['image_url'], $data['remove_image']);
        $this->homepage->clearCache();

        return $data;
    }
}

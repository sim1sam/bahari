<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeFeature;
use App\Services\HomepageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeFeatureController extends Controller
{
    public function __construct(private HomepageContentService $homepage) {}

    public function index(): View
    {
        return view('admin.homepage.features.index', [
            'features' => HomeFeature::orderBy('sort_order')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.homepage.features.form', ['feature' => new HomeFeature]);
    }

    public function store(Request $request): RedirectResponse
    {
        HomeFeature::create($this->validated($request));
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.features.index')->with('success', 'Feature created.');
    }

    public function edit(HomeFeature $feature): View
    {
        return view('admin.homepage.features.form', compact('feature'));
    }

    public function update(Request $request, HomeFeature $feature): RedirectResponse
    {
        $feature->update($this->validated($request));
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.features.index')->with('success', 'Feature updated.');
    }

    public function destroy(HomeFeature $feature): RedirectResponse
    {
        $feature->delete();
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.features.index')->with('success', 'Feature deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:255',
            'icon' => ['required', Rule::in(array_keys(HomeFeature::ICONS))],
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private MediaStorageService $media) {}

    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::withCount('products')->orderBy('sort_order')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validateCategory($request));

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validateCategory($request, $category));

        return redirect()->route('admin.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->media->delete($category->image);
        $this->media->delete($category->card_image);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Category deleted.');
    }

    private function validateCategory(Request $request, ?Category $category = null): array
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:100|unique:categories,slug,'.($category?->id ?? 'NULL'),
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:30',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
            'image_url' => 'nullable|url|max:500',
            'card_image' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:3072',
            'card_image_url' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_sale' => 'boolean',
            'is_active' => 'boolean',
            'remove_image' => 'boolean',
            'remove_card_image' => 'boolean',
        ]);

        $data = collect($validated)->except([
            'image', 'image_url', 'card_image', 'card_image_url', 'remove_image', 'remove_card_image',
        ])->all();

        $data['is_sale'] = $request->boolean('is_sale');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $data['image'] = $this->resolveImageField(
            $request,
            'image',
            'image_url',
            'remove_image',
            $category?->image
        );

        $data['card_image'] = $this->resolveImageField(
            $request,
            'card_image',
            'card_image_url',
            'remove_card_image',
            $category?->card_image
        );

        return $data;
    }

    private function resolveImageField(
        Request $request,
        string $fileKey,
        string $urlKey,
        string $removeKey,
        ?string $current
    ): ?string {
        if ($request->boolean($removeKey)) {
            $this->media->delete($current);

            return null;
        }

        $file = $request->file($fileKey);

        if ($file && $file->getError() !== UPLOAD_ERR_NO_FILE) {
            return $this->media->storeUpload(
                $file,
                'categories',
                $current,
                $fileKey
            );
        }

        if ($request->filled($urlKey)) {
            return $this->media->storeFromUrl(
                $request->input($urlKey),
                'categories',
                $current,
                $urlKey
            );
        }

        return $this->media->storedPath($current);
    }
}

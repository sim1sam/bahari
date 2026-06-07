<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
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
            'image' => 'nullable|url|max:500',
            'card_image' => 'nullable|url|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_sale' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_sale'] = $request->boolean('is_sale');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        return $validated;
    }
}

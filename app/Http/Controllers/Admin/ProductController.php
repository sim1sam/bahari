<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => Product::with('category')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        Product::create($validated);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validateProduct($request, $product));

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'slug' => 'required|string|max:100|unique:products,slug,'.($product?->id ?? 'NULL'),
            'name' => 'required|string|max:200',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|url|max:500',
            'badge' => 'nullable|string|max:30',
            'badge_variant' => 'nullable|string|max:30',
            'rating' => 'nullable|numeric|min:0|max:5',
            'description' => 'nullable|string',
            'sizes' => 'nullable|string',
            'colors' => 'nullable|string',
            'is_featured' => 'boolean',
            'is_new_arrival' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['sizes'] = $this->toArray($validated['sizes'] ?? 'XS,S,M,L,XL');
        $validated['colors'] = $this->toArray($validated['colors'] ?? 'Black,White,Rose');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_new_arrival'] = $request->boolean('is_new_arrival');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['images'] = $validated['image'] ? [$validated['image']] : [];

        return $validated;
    }

    private function toArray(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}

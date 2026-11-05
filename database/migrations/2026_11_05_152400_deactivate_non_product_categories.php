<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Category::query()
            ->get(['id', 'name', 'slug', 'is_active'])
            ->each(function (Category $category) {
                if ($category->isNonProduct() && $category->is_active) {
                    $category->update(['is_active' => false]);
                }
            });
    }

    public function down(): void
    {
        // Intentionally empty — do not re-activate payment categories.
    }
};

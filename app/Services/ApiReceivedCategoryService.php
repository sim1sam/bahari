<?php

namespace App\Services;

use App\Models\ApiReceivedItem;
use App\Models\Category;
use Illuminate\Support\Str;

class ApiReceivedCategoryService
{
    public function register(?string $name): ?Category
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $existing = Category::query()
            ->where(function ($query) use ($name) {
                $query->where('name', $name)
                    ->orWhere('slug', Str::slug($name));
            })
            ->first();

        if ($existing) {
            return $existing;
        }

        return Category::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'is_active' => true,
            'sort_order' => ((int) Category::max('sort_order')) + 1,
        ]);
    }

    public function attachToItem(ApiReceivedItem $item, ?string $categoryName): void
    {
        $categoryName = trim((string) $categoryName);

        if ($categoryName === '') {
            return;
        }

        $category = $this->register($categoryName);
        $stored = $item->getAttributes();
        $updates = [];

        if (($stored['category_name'] ?? null) !== $categoryName) {
            $updates['category_name'] = $categoryName;
        }

        if (ApiReceivedItem::hasCategoryRelationColumn() && ($stored['category_id'] ?? null) !== $category->id) {
            $updates['category_id'] = $category->id;
        }

        if ($updates !== []) {
            $item->update($updates);
        }
    }

    public function syncFromReceivedItems(): int
    {
        $synced = 0;

        $columns = ['id', 'category_name', 'payload'];
        if (ApiReceivedItem::hasCategoryRelationColumn()) {
            $columns[] = 'category_id';
        }

        ApiReceivedItem::query()
            ->get($columns)
            ->each(function (ApiReceivedItem $item) use (&$synced) {
                $categoryName = $item->category_name;

                if (! filled($categoryName)) {
                    return;
                }

                $before = $item->getAttributes()['category_id'] ?? null;
                $this->attachToItem($item, $categoryName);

                if (($item->fresh()->getAttributes()['category_id'] ?? null) !== $before) {
                    $synced++;
                }
            });

        return $synced;
    }

    public function resolveCategoryId(?int $categoryId, ?string $name): ?int
    {
        if ($categoryId) {
            return Category::query()
                ->where('is_active', true)
                ->where('id', $categoryId)
                ->value('id');
        }

        if (! $name) {
            return $this->defaultCategoryId();
        }

        $category = $this->register($name);

        return $category?->is_active
            ? $category->id
            : $this->defaultCategoryId();
    }

    public function defaultCategoryId(): ?int
    {
        return Category::query()
            ->where('is_active', true)
            ->where('slug', 'new-in')
            ->value('id')
            ?? Category::query()->where('is_active', true)->orderBy('sort_order')->value('id');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'category';
        $slug = $base;
        $counter = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}

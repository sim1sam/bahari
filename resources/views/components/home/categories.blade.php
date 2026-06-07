@props(['categories' => []])

<section class="py-16 lg:py-20">
    <div class="container-store">
        <x-ui.section-heading
            title="Shop by Category"
            subtitle="Browse our most popular collections"
            actionLabel="View All Categories"
            :actionHref="route('categories.index')"
        />

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach ($categories as $category)
                <x-ecommerce.category-card
                    :name="$category['name']"
                    :count="$category['count'] ?? null"
                    :href="$category['href'] ?? '#'"
                    :image="$category['image'] ?? null"
                    :color="$category['color'] ?? 'brand'"
                />
            @endforeach
        </div>
    </div>
</section>

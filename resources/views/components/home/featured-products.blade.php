@props(['products' => [], 'title' => 'Featured Products', 'subtitle' => 'Handpicked items just for you'])

<section class="py-16 lg:py-20 bg-surface">
    <div class="container-store">
        <x-ui.section-heading
            :title="$title"
            :subtitle="$subtitle"
            actionLabel="See All Products"
        />

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach ($products as $product)
                <x-ecommerce.product-card
                    :name="$product['name']"
                    :price="$product['price']"
                    :slug="$product['slug'] ?? null"
                    :originalPrice="$product['original_price'] ?? null"
                    :image="$product['image'] ?? null"
                    :badge="$product['badge'] ?? null"
                    :badgeVariant="$product['badge_variant'] ?? 'default'"
                    :rating="$product['rating'] ?? null"
                    :href="$product['href'] ?? '#'"
                />
            @endforeach
        </div>
    </div>
</section>

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('sku');
            $table->decimal('original_price', 10, 2)->nullable()->after('price');
            $table->json('images')->nullable()->after('image');
            $table->string('category_name')->nullable()->after('description');
            $table->json('sizes')->nullable()->after('category_name');
            $table->json('colors')->nullable()->after('sizes');
            $table->string('badge')->nullable()->after('colors');
            $table->string('badge_variant')->nullable()->after('badge');
            $table->decimal('rating', 3, 1)->nullable()->after('badge_variant');
            $table->string('processed_image')->nullable()->after('image');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('api_logo')->nullable()->after('api_auto_publish');
        });
    }

    public function down(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->dropColumn([
                'slug', 'original_price', 'images', 'processed_image',
                'category_name', 'sizes', 'colors', 'badge', 'badge_variant', 'rating',
            ]);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('api_logo');
        });
    }
};

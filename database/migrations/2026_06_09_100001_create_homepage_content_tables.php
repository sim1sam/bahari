<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('badge')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('primary_btn')->nullable();
            $table->string('primary_href')->nullable();
            $table->string('secondary_btn')->nullable();
            $table->string('secondary_href')->nullable();
            $table->json('features')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('home_banners', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('badge')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_href')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('home_features', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('icon')->default('truck');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('footer_links', function (Blueprint $table) {
            $table->id();
            $table->string('group', 30);
            $table->string('label');
            $table->string('url');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('top_bar_text')->nullable()->after('youtube_url');
            $table->string('top_bar_text_mobile')->nullable()->after('top_bar_text');
            $table->string('newsletter_title')->nullable()->after('top_bar_text_mobile');
            $table->string('newsletter_text')->nullable()->after('newsletter_title');
            $table->string('footer_shop_title')->nullable()->after('newsletter_text');
            $table->string('footer_support_title')->nullable()->after('footer_shop_title');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'top_bar_text', 'top_bar_text_mobile', 'newsletter_title',
                'newsletter_text', 'footer_shop_title', 'footer_support_title',
            ]);
        });

        Schema::dropIfExists('footer_links');
        Schema::dropIfExists('home_features');
        Schema::dropIfExists('home_banners');
        Schema::dropIfExists('home_sliders');
    }
};

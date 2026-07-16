<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_received_brands', function (Blueprint $table) {
            $table->string('image')->nullable()->after('notes');
        });

        Schema::dropIfExists('shop_page_banners');
    }

    public function down(): void
    {
        Schema::table('api_received_brands', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::create('shop_page_banners', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_href')->nullable();
            $table->string('placement')->default('hero');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['placement', 'is_active', 'sort_order']);
        });
    }
};

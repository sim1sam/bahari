<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('name');
            $table->decimal('purchase_price', 10, 2)->nullable()->after('original_price');
            $table->text('short_description')->nullable()->after('description');
            $table->unsignedInteger('stock')->default(0)->after('colors');
            $table->boolean('is_manual')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'brand',
                'purchase_price',
                'short_description',
                'stock',
                'is_manual',
            ]);
        });
    }
};

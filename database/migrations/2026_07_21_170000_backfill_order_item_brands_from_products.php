<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('order_items', 'brand') || ! Schema::hasColumn('products', 'brand')) {
            return;
        }

        // Copy product.brand onto order lines that still have no brand.
        DB::table('order_items')
            ->join('products', 'products.slug', '=', 'order_items.product_slug')
            ->where(function ($query) {
                $query->whereNull('order_items.brand')
                    ->orWhere('order_items.brand', '');
            })
            ->whereNotNull('products.brand')
            ->where('products.brand', '!=', '')
            ->update([
                'order_items.brand' => DB::raw('products.brand'),
            ]);
    }

    public function down(): void
    {
        // Non-destructive backfill — nothing to reverse.
    }
};

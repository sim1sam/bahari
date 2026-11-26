<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_charges', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('unit', 10)->default('PC');
            $table->decimal('approx_weight', 12, 2)->nullable();
            $table->decimal('actual_rate', 12, 2)->nullable();
            $table->decimal('retail_rate', 12, 2)->default(0);
            $table->decimal('reselling_rate', 12, 2)->nullable();
            $table->decimal('wholesale_rate', 12, 2)->nullable();
            $table->decimal('only_shipping_rate', 12, 2)->nullable();
            $table->decimal('product_factor', 10, 6)->nullable();
            $table->boolean('show_in_cost_calculation')->default(true);
            $table->timestamps();
        });

        Schema::create('cost_calculation_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('conversion_rate', 12, 2)->nullable();
            $table->decimal('default_price_inr', 12, 2)->nullable();
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            ['Pants (Ladies)', 350],
            ['Pants (Gents)', 400],
            ['Kurti / Top / Shirt / T-Shirt', 250],
            ['Kurti Set (3 Pcs)', 450],
            ['Kurti Set / Kurti-Pants Set (2 Pcs)', 400],
            ['Salwar Suit', 500],
            ['Saree (Less than INR 1,999)', 300],
            ['Saree (INR 2,000 or Above)', 400],
            ['Ethnic Saree', 450],
            ['Dress / Western Dress', 400],
            ['Jacket', 500],
            ['Shawl', 250],
            ['Footwear', 350],
            ['Lingerie / Undergarments', 200],
            ['Unstitched Dress Material (UTP)', 350],
            ['Bridal Collection', 800],
        ];

        foreach ($defaults as $i => [$name, $rate]) {
            DB::table('shipping_charges')->insert([
                'item_name' => $name,
                'sort_order' => $i,
                'unit' => 'PC',
                'retail_rate' => $rate,
                'show_in_cost_calculation' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('cost_calculation_settings')->insert([
            'conversion_rate' => 1.45,
            'default_price_inr' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_calculation_settings');
        Schema::dropIfExists('shipping_charges');
    }
};

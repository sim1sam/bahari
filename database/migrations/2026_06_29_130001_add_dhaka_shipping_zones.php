<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('shipping_fee_inside_dhaka', 10, 2)->default(80)->after('theme_background');
            $table->decimal('shipping_fee_outside_dhaka', 10, 2)->default(150)->after('shipping_fee_inside_dhaka');
        });

        if (Schema::hasColumn('site_settings', 'shipping_fee')) {
            DB::table('site_settings')->update([
                'shipping_fee_inside_dhaka' => DB::raw('COALESCE(shipping_fee, 80)'),
                'shipping_fee_outside_dhaka' => DB::raw('COALESCE(shipping_fee, 80) + 70'),
            ]);

            Schema::table('site_settings', function (Blueprint $table) {
                $table->dropColumn('shipping_fee');
            });
        }

        if (! Schema::hasColumn('site_settings', 'free_shipping_threshold')) {
            Schema::table('site_settings', function (Blueprint $table) {
                $table->decimal('free_shipping_threshold', 10, 2)->default(2000)->after('shipping_fee_outside_dhaka');
            });
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_zone', 30)->default('inside_dhaka')->after('shipping');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_zone');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('shipping_fee', 10, 2)->default(120)->after('theme_background');
        });

        DB::table('site_settings')->update([
            'shipping_fee' => DB::raw('shipping_fee_inside_dhaka'),
        ]);

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['shipping_fee_inside_dhaka', 'shipping_fee_outside_dhaka']);
        });
    }
};

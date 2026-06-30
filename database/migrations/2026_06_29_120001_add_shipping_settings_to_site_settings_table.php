<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->decimal('shipping_fee', 10, 2)->default(120)->after('theme_background');
            $table->decimal('free_shipping_threshold', 10, 2)->default(2000)->after('shipping_fee');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['shipping_fee', 'free_shipping_threshold']);
        });
    }
};

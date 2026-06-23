<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('sslcommerz_enabled')->default(false)->after('gtm_enabled');
            $table->boolean('sslcommerz_sandbox')->default(true)->after('sslcommerz_enabled');
            $table->string('sslcommerz_store_id', 100)->nullable()->after('sslcommerz_sandbox');
            $table->text('sslcommerz_store_password')->nullable()->after('sslcommerz_store_id');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'sslcommerz_enabled',
                'sslcommerz_sandbox',
                'sslcommerz_store_id',
                'sslcommerz_store_password',
            ]);
        });
    }
};

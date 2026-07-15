<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('meta_capi_enabled')->default(false)->after('gtm_enabled');
            $table->text('meta_capi_access_token')->nullable()->after('meta_capi_enabled');
            $table->string('meta_capi_test_event_code', 64)->nullable()->after('meta_capi_access_token');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'meta_capi_enabled',
                'meta_capi_access_token',
                'meta_capi_test_event_code',
            ]);
        });
    }
};

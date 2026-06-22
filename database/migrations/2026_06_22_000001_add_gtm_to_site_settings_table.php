<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('gtm_container_id', 20)->nullable()->after('og_image');
            $table->boolean('gtm_enabled')->default(false)->after('gtm_container_id');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['gtm_container_id', 'gtm_enabled']);
        });
    }
};

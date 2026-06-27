<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->longText('processed_image_blob')->nullable()->after('processed_image');
        });
    }

    public function down(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->dropColumn('processed_image_blob');
        });
    }
};

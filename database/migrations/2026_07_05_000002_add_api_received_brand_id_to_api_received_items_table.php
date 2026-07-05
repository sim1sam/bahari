<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->foreignId('api_received_brand_id')
                ->nullable()
                ->after('brand')
                ->constrained('api_received_brands')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('api_received_brand_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            if (! Schema::hasColumn('api_received_items', 'purchase_price')) {
                $table->decimal('purchase_price', 10, 2)->nullable()->after('original_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            if (Schema::hasColumn('api_received_items', 'purchase_price')) {
                $table->dropColumn('purchase_price');
            }
        });
    }
};

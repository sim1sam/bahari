<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('category_name')
                ->constrained('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('api_received_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_expenses', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('reference')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('account_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};

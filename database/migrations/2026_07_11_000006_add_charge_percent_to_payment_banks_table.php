<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_banks', function (Blueprint $table) {
            $table->decimal('charge_percent', 5, 2)->default(0)->after('instructions');
        });
    }

    public function down(): void
    {
        Schema::table('payment_banks', function (Blueprint $table) {
            $table->dropColumn('charge_percent');
        });
    }
};

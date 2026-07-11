<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_banks', function (Blueprint $table) {
            $table->decimal('opening_balance', 12, 2)->default(0)->after('charge_percent');
        });
    }

    public function down(): void
    {
        Schema::table('payment_banks', function (Blueprint $table) {
            $table->dropColumn('opening_balance');
        });
    }
};

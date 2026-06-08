<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type')->default('standard')->after('number');
            $table->string('reference_code')->nullable()->after('payment_method');
            $table->string('bank_name')->nullable()->after('reference_code');
            $table->string('payment_screenshot')->nullable()->after('bank_name');
            $table->text('notes')->nullable()->after('payment_screenshot');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'reference_code', 'bank_name', 'payment_screenshot', 'notes']);
        });
    }
};

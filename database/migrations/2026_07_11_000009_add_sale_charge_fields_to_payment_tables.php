<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('payment_bank_id')->nullable()->after('user_id')->constrained('payment_banks')->nullOnDelete();
            $table->decimal('sale_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('bank_charge_percent', 5, 2)->default(0)->after('sale_amount');
            $table->decimal('bank_charge_amount', 12, 2)->default(0)->after('bank_charge_percent');
        });

        Schema::table('order_payments', function (Blueprint $table) {
            $table->foreignId('payment_bank_id')->nullable()->after('order_id')->constrained('payment_banks')->nullOnDelete();
            $table->foreignId('payment_transaction_id')->nullable()->after('payment_bank_id')->constrained('payment_transactions')->nullOnDelete();
            $table->decimal('sale_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('bank_charge_percent', 5, 2)->default(0)->after('sale_amount');
            $table->decimal('bank_charge_amount', 12, 2)->default(0)->after('bank_charge_percent');
        });

        Schema::table('customer_payments', function (Blueprint $table) {
            $table->decimal('sale_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('bank_charge_percent', 5, 2)->default(0)->after('sale_amount');
            $table->decimal('bank_charge_amount', 12, 2)->default(0)->after('bank_charge_percent');
        });
    }

    public function down(): void
    {
        Schema::table('customer_payments', function (Blueprint $table) {
            $table->dropColumn(['sale_amount', 'bank_charge_percent', 'bank_charge_amount']);
        });

        Schema::table('order_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_transaction_id');
            $table->dropConstrainedForeignId('payment_bank_id');
            $table->dropColumn(['sale_amount', 'bank_charge_percent', 'bank_charge_amount']);
        });

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_bank_id');
            $table->dropColumn(['sale_amount', 'bank_charge_percent', 'bank_charge_amount']);
        });
    }
};

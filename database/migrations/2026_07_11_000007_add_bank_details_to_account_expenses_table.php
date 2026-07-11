<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('account_expenses', function (Blueprint $table) {
            $table->foreignId('payment_bank_id')
                ->nullable()
                ->after('amount')
                ->constrained('payment_banks')
                ->restrictOnDelete();
            $table->decimal('bank_charge_percent', 5, 2)->default(0)->after('payment_bank_id');
            $table->decimal('bank_charge_amount', 12, 2)->default(0)->after('bank_charge_percent');
            $table->decimal('total_deduction', 12, 2)->default(0)->after('bank_charge_amount');
        });

        DB::table('account_expenses')->update([
            'total_deduction' => DB::raw('amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('account_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_bank_id');
            $table->dropColumn([
                'bank_charge_percent',
                'bank_charge_amount',
                'total_deduction',
            ]);
        });
    }
};

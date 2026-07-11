<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->string('type', 40);
            $table->string('direction', 10);
            $table->string('source_type', 80);
            $table->unsignedBigInteger('source_id');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_bank_id')->nullable()->constrained('payment_banks')->nullOnDelete();
            $table->foreignId('counterparty_bank_id')->nullable()->constrained('payment_banks')->nullOnDelete();
            $table->foreignId('account_head_id')->nullable()->constrained('account_heads')->nullOnDelete();
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('bank_charge_percent', 5, 2)->default(0);
            $table->decimal('bank_charge_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('reference', 100)->nullable();
            $table->string('description')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['source_type', 'source_id']);
            $table->index(['transaction_date', 'type']);
            $table->index(['payment_bank_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};

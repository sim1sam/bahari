<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_inter_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_bank_id')->constrained('payment_banks')->restrictOnDelete();
            $table->foreignId('to_bank_id')->constrained('payment_banks')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('transfer_date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('transfer_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_inter_transfers');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('label')->nullable();
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('discount_value', 10, 2);
            $table->enum('audience', ['public', 'customers'])->default('public');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('per_customer_limit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupon_customer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['coupon_id', 'user_id']);
        });

        $now = now();

        DB::table('coupons')->insert([
            [
                'code' => 'LUXE10',
                'label' => '10% off your order',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'audience' => 'public',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'LUXE20',
                'label' => '20% off your order',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'audience' => 'public',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SAVE15',
                'label' => '৳15 off your order',
                'discount_type' => 'fixed',
                'discount_value' => 15,
                'audience' => 'public',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FASHION',
                'label' => '15% off fashion items',
                'discount_type' => 'percent',
                'discount_value' => 15,
                'audience' => 'public',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_customer');
        Schema::dropIfExists('coupons');
    }
};

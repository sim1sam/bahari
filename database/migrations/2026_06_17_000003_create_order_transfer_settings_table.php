<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_transfer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->nullable();
            $table->string('domain')->nullable();
            $table->string('endpoint_path')->default('/api/orders/import');
            $table->string('api_key')->nullable();
            $table->text('access_token')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('external_transfer_status')->default('pending')->after('payment_status');
            $table->text('external_transfer_message')->nullable()->after('external_transfer_status');
            $table->timestamp('external_transferred_at')->nullable()->after('external_transfer_message');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'external_transfer_status',
                'external_transfer_message',
                'external_transferred_at',
            ]);
        });

        Schema::dropIfExists('order_transfer_settings');
    }
};

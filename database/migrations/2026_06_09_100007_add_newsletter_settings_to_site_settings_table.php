<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('newsletter_enabled')->default(true)->after('newsletter_button_text');
            $table->string('newsletter_success_message')->nullable()->after('newsletter_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['newsletter_enabled', 'newsletter_success_message']);
        });
    }
};

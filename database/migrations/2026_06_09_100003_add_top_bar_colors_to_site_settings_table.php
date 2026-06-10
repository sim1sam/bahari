<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('top_bar_bg_color', 20)->nullable()->after('top_bar_text_mobile');
            $table->string('top_bar_text_color', 20)->nullable()->after('top_bar_bg_color');
            $table->string('top_bar_link_color', 20)->nullable()->after('top_bar_text_color');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['top_bar_bg_color', 'top_bar_text_color', 'top_bar_link_color']);
        });
    }
};

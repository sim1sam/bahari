<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('theme_primary', 20)->nullable()->after('footer_copyright');
            $table->string('theme_primary_dark', 20)->nullable()->after('theme_primary');
            $table->string('theme_footer_bg', 20)->nullable()->after('theme_primary_dark');
            $table->string('theme_text', 20)->nullable()->after('theme_footer_bg');
            $table->string('theme_background', 20)->nullable()->after('theme_text');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'theme_primary', 'theme_primary_dark', 'theme_footer_bg',
                'theme_text', 'theme_background',
            ]);
        });
    }
};

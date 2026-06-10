<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('newsletter_placeholder')->nullable()->after('newsletter_text');
            $table->string('newsletter_button_text')->nullable()->after('newsletter_placeholder');
            $table->string('footer_copyright')->nullable()->after('footer_support_title');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['newsletter_placeholder', 'newsletter_button_text', 'footer_copyright']);
        });
    }
};

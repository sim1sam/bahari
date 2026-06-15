<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_sources', function (Blueprint $table) {
            $table->string('base_url')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('api_sources', function (Blueprint $table) {
            $table->dropColumn('base_url');
        });
    }
};

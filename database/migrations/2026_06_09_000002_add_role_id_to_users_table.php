<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
        });

        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');
        $customerRoleId = DB::table('roles')->where('slug', 'customer')->value('id');

        if ($adminRoleId && $customerRoleId) {
            DB::table('users')->where('is_admin', true)->update(['role_id' => $adminRoleId]);
            DB::table('users')->where('is_admin', false)->update(['role_id' => $customerRoleId]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');

        if ($adminRoleId) {
            DB::table('users')->where('role_id', $adminRoleId)->update(['is_admin' => true]);
            DB::table('users')->where('role_id', '!=', $adminRoleId)->update(['is_admin' => false]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};

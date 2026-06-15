<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')
            ->where('can_access_admin', true)
            ->orderBy('id')
            ->each(function ($role) {
                $permissions = json_decode($role->permissions, true) ?? [];

                if (in_array('database_migration', $permissions, true)) {
                    return;
                }

                $permissions[] = 'database_migration';

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['permissions' => json_encode(array_values($permissions))]);
            });
    }

    public function down(): void
    {
        DB::table('roles')
            ->where('can_access_admin', true)
            ->orderBy('id')
            ->each(function ($role) {
                $permissions = array_values(array_filter(
                    json_decode($role->permissions, true) ?? [],
                    fn ($key) => $key !== 'database_migration'
                ));

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update(['permissions' => json_encode($permissions)]);
            });
    }
};

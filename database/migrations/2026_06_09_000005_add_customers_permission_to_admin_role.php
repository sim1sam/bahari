<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $role = DB::table('roles')->where('slug', 'admin')->first();

        if (! $role) {
            return;
        }

        $permissions = json_decode($role->permissions ?? '[]', true) ?: [];

        if (! in_array('customers', $permissions, true)) {
            $permissions[] = 'customers';
            sort($permissions);

            DB::table('roles')->where('id', $role->id)->update([
                'permissions' => json_encode(array_values($permissions)),
            ]);
        }
    }

    public function down(): void
    {
        $role = DB::table('roles')->where('slug', 'admin')->first();

        if (! $role) {
            return;
        }

        $permissions = array_values(array_filter(
            json_decode($role->permissions ?? '[]', true) ?: [],
            fn ($p) => $p !== 'customers'
        ));

        DB::table('roles')->where('id', $role->id)->update([
            'permissions' => json_encode($permissions),
        ]);
    }
};

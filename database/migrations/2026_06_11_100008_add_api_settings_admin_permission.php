<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $adminRole = Role::where('slug', Role::SLUG_ADMIN)->first();
        if (! $adminRole) {
            return;
        }

        $permissions = $adminRole->permissions ?? [];

        if (! in_array('api_settings', $permissions, true)) {
            $permissions[] = 'api_settings';
            sort($permissions);
            $adminRole->update(['permissions' => $permissions]);
        }
    }

    public function down(): void
    {
        $adminRole = Role::where('slug', Role::SLUG_ADMIN)->first();
        if (! $adminRole) {
            return;
        }

        $permissions = array_values(array_filter(
            $adminRole->permissions ?? [],
            fn ($p) => $p !== 'api_settings'
        ));

        $adminRole->update(['permissions' => $permissions]);
    }
};

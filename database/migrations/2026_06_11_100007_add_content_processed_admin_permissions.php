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

        $permissions = array_values(array_filter(
            $adminRole->permissions ?? [],
            fn ($p) => $p !== 'api_received'
        ));

        foreach (['api_content', 'api_processed'] as $perm) {
            if (! in_array($perm, $permissions, true)) {
                $permissions[] = $perm;
            }
        }

        sort($permissions);
        $adminRole->update(['permissions' => $permissions]);
    }

    public function down(): void
    {
        $adminRole = Role::where('slug', Role::SLUG_ADMIN)->first();
        if (! $adminRole) {
            return;
        }

        $permissions = array_values(array_filter(
            $adminRole->permissions ?? [],
            fn ($p) => ! in_array($p, ['api_content', 'api_processed'], true)
        ));

        if (! in_array('api_received', $permissions, true)) {
            $permissions[] = 'api_received';
        }

        sort($permissions);
        $adminRole->update(['permissions' => $permissions]);
    }
};

<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Role::query()
            ->where('can_access_admin', true)
            ->each(function (Role $role) {
                $permissions = $role->permissions ?? [];

                if (! is_array($permissions)) {
                    $permissions = [];
                }

                if (! in_array('cache_clear', $permissions, true)) {
                    $permissions[] = 'cache_clear';
                    $role->update(['permissions' => array_values($permissions)]);
                }
            });
    }

    public function down(): void
    {
        Role::query()
            ->where('can_access_admin', true)
            ->each(function (Role $role) {
                $permissions = $role->permissions ?? [];

                if (! is_array($permissions)) {
                    return;
                }

                $role->update([
                    'permissions' => array_values(array_filter(
                        $permissions,
                        fn ($key) => $key !== 'cache_clear'
                    )),
                ]);
            });
    }
};

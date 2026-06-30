<?php

use App\Models\Role;
use App\Support\AdminFeatures;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $role = Role::query()->where('slug', Role::SLUG_ADMIN)->first();

        if (! $role) {
            return;
        }

        $permissions = array_values(array_unique(array_merge(
            $role->permissions ?? [],
            AdminFeatures::keys()
        )));

        $role->update(['permissions' => $permissions]);
    }

    public function down(): void
    {
        $role = Role::query()->where('slug', Role::SLUG_ADMIN)->first();

        if (! $role) {
            return;
        }

        $permissions = array_values(array_filter(
            $role->permissions ?? [],
            fn (string $key) => $key !== 'reports'
        ));

        $role->update(['permissions' => $permissions]);
    }
};

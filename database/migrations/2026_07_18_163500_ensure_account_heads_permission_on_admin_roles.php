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

                if (! in_array('account_heads', $permissions, true)) {
                    $permissions[] = 'account_heads';
                    $role->update(['permissions' => array_values($permissions)]);
                }
            });
    }

    public function down(): void
    {
        // Keep permission — removing would hide the Account menu again.
    }
};

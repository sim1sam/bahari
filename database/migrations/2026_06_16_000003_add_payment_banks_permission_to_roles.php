<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Role::query()->where('can_access_admin', true)->get()->each(function (Role $role) {
            $permissions = $this->permissions($role);

            if (! in_array('payment_banks', $permissions, true)) {
                $permissions[] = 'payment_banks';
                $role->update(['permissions' => array_values($permissions)]);
            }
        });
    }

    public function down(): void
    {
        Role::query()->where('can_access_admin', true)->get()->each(function (Role $role) {
            $permissions = $this->permissions($role);
            $permissions = array_values(array_filter($permissions, fn ($permission) => $permission !== 'payment_banks'));
            $role->update(['permissions' => $permissions]);
        });
    }

    /** @return array<int, string> */
    private function permissions(Role $role): array
    {
        if (is_array($role->permissions)) {
            return $role->permissions;
        }

        return json_decode($role->permissions ?? '[]', true) ?: [];
    }
};

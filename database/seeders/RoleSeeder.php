<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Support\AdminFeatures;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['slug' => Role::SLUG_ADMIN],
            [
                'name' => 'Admin',
                'description' => 'Full access to the admin panel',
                'can_access_admin' => true,
                'is_active' => true,
                'permissions' => AdminFeatures::keys(),
            ]
        );

        Role::updateOrCreate(
            ['slug' => Role::SLUG_CUSTOMER],
            [
                'name' => 'Customer',
                'description' => 'Storefront customer account',
                'can_access_admin' => false,
                'is_active' => true,
                'permissions' => null,
            ]
        );
    }
}

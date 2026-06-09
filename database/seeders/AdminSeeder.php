<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@ecommerce.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role_id' => Role::where('slug', Role::SLUG_ADMIN)->value('id'),
            ]
        );
    }
}

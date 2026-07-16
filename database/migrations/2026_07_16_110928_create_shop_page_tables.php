<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_page_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->string('hero_title')->default('Shop');
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_cta_label')->nullable();
            $table->string('section_title')->nullable();
            $table->string('section_subtitle')->nullable();
            $table->boolean('show_all_when_empty')->default(true);
            $table->json('featured_product_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('shop_page_brand_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });

        Role::query()->where('can_access_admin', true)->get()->each(function (Role $role) {
            $permissions = is_array($role->permissions)
                ? $role->permissions
                : (json_decode($role->permissions ?? '[]', true) ?: []);

            if (! in_array('shop_page', $permissions, true)) {
                $permissions[] = 'shop_page';
                $role->update(['permissions' => array_values($permissions)]);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_page_brand_schedules');
        Schema::dropIfExists('shop_page_settings');

        Role::query()->where('can_access_admin', true)->get()->each(function (Role $role) {
            $permissions = is_array($role->permissions)
                ? $role->permissions
                : (json_decode($role->permissions ?? '[]', true) ?: []);

            $permissions = array_values(array_filter(
                $permissions,
                fn ($permission) => $permission !== 'shop_page'
            ));

            $role->update(['permissions' => $permissions]);
        });
    }
};

<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_received_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_source_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('title');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->unique(['api_source_id', 'source_id']);
        });

        $adminRole = Role::where('slug', Role::SLUG_ADMIN)->first();
        if ($adminRole) {
            $permissions = $adminRole->permissions ?? [];
            if (! in_array('api_received', $permissions, true)) {
                $permissions[] = 'api_received';
                $adminRole->update(['permissions' => $permissions]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('api_received_items');

        $adminRole = Role::where('slug', Role::SLUG_ADMIN)->first();
        if ($adminRole) {
            $permissions = array_values(array_filter(
                $adminRole->permissions ?? [],
                fn ($p) => $p !== 'api_received'
            ));
            $adminRole->update(['permissions' => $permissions]);
        }
    }
};

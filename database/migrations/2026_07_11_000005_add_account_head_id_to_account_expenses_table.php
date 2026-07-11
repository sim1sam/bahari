<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $legacyCategories = [
        'inventory' => 'Product Purchase / Inventory',
        'rent' => 'Rent',
        'salary' => 'Salary & Wages',
        'marketing' => 'Marketing',
        'shipping' => 'Shipping & Delivery',
        'utilities' => 'Utilities',
        'supplies' => 'Supplies',
        'maintenance' => 'Maintenance',
        'other' => 'Other',
    ];

    public function up(): void
    {
        Schema::table('account_expenses', function (Blueprint $table) {
            $table->foreignId('account_head_id')
                ->nullable()
                ->after('expense_date')
                ->constrained('account_heads')
                ->restrictOnDelete();
        });

        $expenseTypeId = DB::table('account_head_types')->where('slug', 'expense')->value('id');
        $headIdsByCategory = [];

        foreach ($this->legacyCategories as $slug => $name) {
            $code = strtoupper($slug);

            $existingId = DB::table('account_heads')->where('code', $code)->value('id');

            if ($existingId) {
                $headIdsByCategory[$slug] = $existingId;

                continue;
            }

            $headIdsByCategory[$slug] = DB::table('account_heads')->insertGetId([
                'name' => $name,
                'code' => $code,
                'account_head_type_id' => $expenseTypeId,
                'description' => null,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $fallbackHeadId = $headIdsByCategory['other'] ?? array_values($headIdsByCategory)[0] ?? null;

        DB::table('account_expenses')
            ->orderBy('id')
            ->get(['id', 'category'])
            ->each(function ($expense) use ($headIdsByCategory, $fallbackHeadId) {
                DB::table('account_expenses')
                    ->where('id', $expense->id)
                    ->update([
                        'account_head_id' => $headIdsByCategory[$expense->category] ?? $fallbackHeadId,
                    ]);
            });

        Schema::table('account_expenses', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
            $table->unsignedBigInteger('account_head_id')->nullable(false)->change();
            $table->index('account_head_id');
        });
    }

    public function down(): void
    {
        Schema::table('account_expenses', function (Blueprint $table) {
            $table->string('category', 60)->nullable()->after('expense_date');
        });

        $headsById = DB::table('account_heads')->pluck('code', 'id');

        DB::table('account_expenses')
            ->orderBy('id')
            ->get(['id', 'account_head_id'])
            ->each(function ($expense) use ($headsById) {
                $code = strtolower((string) ($headsById[$expense->account_head_id] ?? 'other'));

                DB::table('account_expenses')
                    ->where('id', $expense->id)
                    ->update(['category' => array_key_exists($code, $this->legacyCategories) ? $code : 'other']);
            });

        Schema::table('account_expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_head_id');
            $table->string('category', 60)->nullable(false)->change();
            $table->index('category');
        });
    }
};

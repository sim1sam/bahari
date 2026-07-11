<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('account_heads', 'account_head_type_id')) {
            Schema::table('account_heads', function (Blueprint $table) {
                $table->foreignId('account_head_type_id')
                    ->nullable()
                    ->after('code')
                    ->constrained('account_head_types')
                    ->restrictOnDelete();
            });
        }

        if (Schema::hasColumn('account_heads', 'type')) {
            $typeIdsBySlug = DB::table('account_head_types')->pluck('id', 'slug');

            DB::table('account_heads')
                ->orderBy('id')
                ->get(['id', 'type'])
                ->each(function ($head) use ($typeIdsBySlug) {
                    $typeId = $typeIdsBySlug[$head->type] ?? $typeIdsBySlug['expense'];

                    DB::table('account_heads')
                        ->where('id', $head->id)
                        ->update(['account_head_type_id' => $typeId]);
                });

            Schema::table('account_heads', function (Blueprint $table) {
                $table->dropIndex(['type']);
                $table->dropColumn('type');
            });
        }

        $defaultTypeId = DB::table('account_head_types')->where('slug', 'expense')->value('id');

        if ($defaultTypeId) {
            DB::table('account_heads')
                ->whereNull('account_head_type_id')
                ->update(['account_head_type_id' => $defaultTypeId]);
        }

        Schema::table('account_heads', function (Blueprint $table) {
            $table->dropForeign(['account_head_type_id']);
        });

        Schema::table('account_heads', function (Blueprint $table) {
            $table->unsignedBigInteger('account_head_type_id')->nullable(false)->change();
            $table->foreign('account_head_type_id')
                ->references('id')
                ->on('account_head_types')
                ->restrictOnDelete();
        });

        if (! $this->hasIndex('account_heads', 'account_heads_account_head_type_id_index')) {
            Schema::table('account_heads', function (Blueprint $table) {
                $table->index('account_head_type_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('account_heads', function (Blueprint $table) {
            $table->string('type', 20)->nullable()->after('code');
        });

        $slugsById = DB::table('account_head_types')->pluck('slug', 'id');

        DB::table('account_heads')
            ->orderBy('id')
            ->get(['id', 'account_head_type_id'])
            ->each(function ($head) use ($slugsById) {
                DB::table('account_heads')
                    ->where('id', $head->id)
                    ->update(['type' => $slugsById[$head->account_head_type_id] ?? 'expense']);
            });

        Schema::table('account_heads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('account_head_type_id');
            $table->string('type', 20)->nullable(false)->change();
            $table->index('type');
        });
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);

        return count($indexes) > 0;
    }
};

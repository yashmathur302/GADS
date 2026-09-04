<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Keywords now attach to a niche (a sub-category within an industry,
     * e.g. "Preschool" under Education) rather than the industry directly —
     * broad industries mixed unrelated keywords together at the industry
     * level. No production data exists yet, so this replaces the column
     * rather than migrating rows.
     */
    public function up(): void
    {
        Schema::table('keyword_vault_entries', function (Blueprint $table) {
            $table->dropForeign(['industry_id']);
            $table->dropIndex(['industry_id', 'type']);
            $table->dropColumn('industry_id');
        });

        Schema::table('keyword_vault_entries', function (Blueprint $table) {
            $table->foreignId('niche_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['niche_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keyword_vault_entries', function (Blueprint $table) {
            $table->dropForeign(['niche_id']);
            $table->dropIndex(['niche_id', 'type']);
            $table->dropColumn('niche_id');
        });

        Schema::table('keyword_vault_entries', function (Blueprint $table) {
            $table->foreignId('industry_id')->after('id')->constrained()->cascadeOnDelete();
            $table->index(['industry_id', 'type']);
        });
    }
};

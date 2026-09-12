<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Which section this client belongs to: 'keywords',
            // 'negative-keywords', or 'location'. Clients are no longer
            // shared across sections — each is scoped to exactly one, so
            // deleting a client in one section can never touch data in
            // another. Nullable only so this migration doesn't fail
            // against existing rows; the app always sets it on create.
            $table->string('type')->nullable()->after('industry_category');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};

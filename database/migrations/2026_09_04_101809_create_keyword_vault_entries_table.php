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
        Schema::create('keyword_vault_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('industry_id')->constrained()->cascadeOnDelete();
            // 'keyword' (Keyword Vault) or 'negative' (Negative Keyword Vault) —
            // see App\Enums\KeywordVaultType.
            $table->string('type');
            $table->string('keyword');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['industry_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keyword_vault_entries');
    }
};

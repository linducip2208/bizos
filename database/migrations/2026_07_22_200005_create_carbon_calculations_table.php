<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('period'); // 2026-01, 2026-Q1, 2026
            $table->string('scope'); // scope1, scope2, scope3, total
            $table->decimal('emissions_tco2e', 15, 4);
            $table->json('breakdown'); // per-source breakdown data
            $table->json('emission_factors_used'); // factors applied for transparency
            $table->json('source_data'); // raw data references
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'period', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_calculations');
    }
};

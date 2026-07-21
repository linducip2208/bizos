<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->default('general');
            $table->string('calculation_formula');
            $table->decimal('target_value', 20, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('data_source')->nullable();
            $table->enum('update_frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'category']);
        });

        Schema::create('kpi_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_definition_id')->constrained('kpi_definitions')->cascadeOnDelete();
            $table->string('period');
            $table->decimal('value', 20, 2)->default(0);
            $table->decimal('target', 20, 2)->nullable();
            $table->enum('status', ['on_track', 'at_risk', 'behind'])->default('on_track');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['kpi_definition_id', 'period']);
            $table->index('calculated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_values');
        Schema::dropIfExists('kpi_definitions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sod_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('sensitive_function');
            $table->string('conflicting_function');
            $table->string('risk_level'); // low, medium, high, critical
            $table->text('description')->nullable();
            $table->text('compensating_controls')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system_default')->default(false);
            $table->string('category')->nullable(); // procurement, finance, payroll, hr, asset, inventory, sales
            $table->timestamps();

            $table->unique(['sensitive_function', 'conflicting_function']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sod_rules');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->enum('scenario_type', ['best_case', 'base_case', 'worst_case', 'custom'])->default('base_case');
            $table->text('description')->nullable();
            $table->json('assumptions')->nullable();
            $table->foreignId('parent_budget_id')->nullable()->constrained('budgets')->nullOnDelete();
            $table->boolean('is_active')->default(false);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenarios');
    }
};

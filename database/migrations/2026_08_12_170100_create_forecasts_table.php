<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->enum('forecast_type', ['revenue', 'expense', 'cash_flow', 'all'])->default('all');
            $table->smallInteger('fiscal_year');
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('frequency', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->integer('version')->default(1);
            $table->boolean('is_rolling')->default(false);
            $table->foreignId('baseline_budget_id')->nullable()->constrained('budgets')->nullOnDelete();
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecasts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forecast_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_id')->constrained('forecasts')->cascadeOnDelete();
            $table->foreignId('coa_id')->nullable()->constrained('coa')->nullOnDelete();
            $table->string('description', 500)->nullable();
            $table->date('period_date');
            $table->decimal('planned_amount', 20, 2)->default(0);
            $table->decimal('best_case_amount', 20, 2)->nullable();
            $table->decimal('worst_case_amount', 20, 2)->nullable();
            $table->decimal('probability_percent', 5, 2)->nullable();
            $table->json('assumptions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_items');
    }
};

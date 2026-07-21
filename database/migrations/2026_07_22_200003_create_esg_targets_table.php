<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esg_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('category'); // environmental, social, governance
            $table->string('metric'); // carbon_emissions, waste_reduction, water_reduction, gender_diversity, safety_incident, board_independence
            $table->string('metric_label');
            $table->string('unit'); // tCO2e, kg, m3, %, rate, count
            $table->decimal('baseline_value', 15, 4)->nullable();
            $table->decimal('target_value', 15, 4);
            $table->decimal('current_value', 15, 4)->default(0);
            $table->date('deadline');
            $table->string('status')->default('on_track'); // on_track, at_risk, behind, achieved, abandoned
            $table->text('description')->nullable();
            $table->string('responsible_person')->nullable();
            $table->string('framework_reference')->nullable(); // gri_302, sasb_em_ep, pojk_51, etc
            $table->json('progress_history')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esg_targets');
    }
};

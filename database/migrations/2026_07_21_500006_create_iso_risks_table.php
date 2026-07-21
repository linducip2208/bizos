<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_risks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('asset_name');
            $table->string('asset_type'); // hardware, software, data, network, people, facility
            $table->text('asset_description')->nullable();
            $table->string('threat');
            $table->string('vulnerability');
            $table->enum('likelihood', ['rare', 'unlikely', 'possible', 'likely', 'almost_certain']);
            $table->enum('impact', ['insignificant', 'minor', 'moderate', 'major', 'catastrophic']);
            $table->string('risk_level'); // low, medium, high, critical
            $table->integer('risk_score')->default(0); // 1-25
            $table->text('existing_controls')->nullable();
            $table->string('treatment'); // accept, mitigate, transfer, avoid
            $table->text('treatment_plan')->nullable();
            $table->text('applied_controls')->nullable();
            $table->string('iso_control_ref')->nullable(); // A.5.1.1, A.8.2.3, etc.
            $table->string('status')->default('open'); // open, in_treatment, treated, accepted, closed
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('review_due')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_risks');
    }
};

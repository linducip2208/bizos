<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dpia_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('processing_activity');
            $table->text('description')->nullable();
            $table->string('data_controller')->nullable();
            $table->string('data_processor')->nullable();
            $table->json('data_types')->nullable(); // [personal, sensitive, financial, biometric, health, children]
            $table->json('data_subjects')->nullable(); // [employees, clients, suppliers, public]
            $table->json('risks')->nullable(); // [{risk, likelihood, impact, severity}]
            $table->json('mitigations')->nullable();
            $table->text('necessity_proportionality')->nullable();
            $table->enum('status', ['draft', 'in_review', 'approved', 'rejected', 'needs_revision'])->default('draft');
            $table->string('risk_level')->default('low'); // low, medium, high, critical
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dpia_assessments');
    }
};

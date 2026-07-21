<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained('job_postings')->restrictOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 255);
            $table->string('phone', 30)->nullable();
            $table->string('photo', 255)->nullable();
            $table->string('resume_path', 255)->nullable();
            $table->string('portfolio_url', 255)->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->string('source', 255)->nullable();
            $table->decimal('expected_salary', 20, 2)->nullable();
            $table->date('available_date')->nullable();
            $table->enum('pipeline_stage', ['applied', 'screening', 'hr_interview', 'user_interview', 'technical_test', 'offering', 'hired', 'rejected', 'withdrawn'])->default('applied');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('hired_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};

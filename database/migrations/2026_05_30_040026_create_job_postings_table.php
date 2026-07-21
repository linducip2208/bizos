<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
            $table->enum('employee_type', ['permanent', 'contract', 'probation', 'intern', 'freelance'])->nullable();
            $table->decimal('min_salary', 20, 2)->nullable();
            $table->decimal('max_salary', 20, 2)->nullable();
            $table->string('location', 255)->nullable();
            $table->boolean('is_remote')->default(false);
            $table->integer('quota')->default(1);
            $table->enum('status', ['draft', 'published', 'closed', 'cancelled'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};

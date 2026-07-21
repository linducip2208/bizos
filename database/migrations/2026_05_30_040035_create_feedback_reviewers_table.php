<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('feedback_cycles')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('employees')->restrictOnDelete();
            $table->enum('reviewer_type', ['self', 'supervisor', 'peer', 'subordinate']);
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_reviewers');
    }
};

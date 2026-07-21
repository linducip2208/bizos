<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->foreignId('interviewer_id')->constrained('interviewers')->cascadeOnDelete();
            $table->decimal('rating', 3, 1);
            $table->text('comments')->nullable();
            $table->enum('recommendation', ['strong_hire', 'hire', 'maybe', 'reject', 'strong_reject'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_results');
    }
};

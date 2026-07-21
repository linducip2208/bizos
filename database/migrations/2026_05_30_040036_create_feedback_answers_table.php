<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('feedback_reviewers')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('feedback_questions')->cascadeOnDelete();
            $table->decimal('rating', 3, 1)->nullable();
            $table->text('text_answer')->nullable();
            $table->json('selected_options')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_answers');
    }
};

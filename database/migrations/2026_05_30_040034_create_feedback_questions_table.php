<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('feedback_cycles')->cascadeOnDelete();
            $table->text('question');
            $table->enum('category', ['technical', 'soft_skill', 'leadership', 'communication', 'teamwork', 'initiative']);
            $table->enum('question_type', ['rating', 'text', 'multiple_choice'])->default('rating');
            $table->json('options')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_questions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamification_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('action_id')->nullable()->constrained('gamification_actions')->nullOnDelete();
            $table->string('action_key');
            $table->integer('points');
            $table->json('context')->nullable();
            $table->date('period_date')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'action_key']);
            $table->index(['user_id', 'period_date']);
            $table->index(['company_id', 'period_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamification_points');
    }
};

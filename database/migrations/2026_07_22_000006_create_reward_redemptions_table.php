<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reward_id')->constrained()->cascadeOnDelete();
            $table->integer('points_spent');
            $table->string('status')->default('pending'); // pending, approved, rejected, fulfilled
            $table->text('notes')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['reward_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
    }
};

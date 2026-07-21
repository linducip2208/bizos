<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gamification_badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('heroicon-o-star');
            $table->string('category')->nullable();
            $table->string('trigger_action')->nullable();
            $table->integer('trigger_count')->nullable();
            $table->decimal('threshold_value', 15, 2)->nullable();
            $table->string('threshold_unit')->nullable();
            $table->integer('points_reward')->default(0);
            $table->string('color')->default('indigo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gamification_badges');
    }
};

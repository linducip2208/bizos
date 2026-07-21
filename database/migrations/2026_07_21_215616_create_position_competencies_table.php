<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('competency_id')->constrained('competencies')->cascadeOnDelete();
            $table->tinyInteger('required_level')->default(1);
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->timestamps();
            $table->unique(['position_id', 'competency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('position_competencies');
    }
};

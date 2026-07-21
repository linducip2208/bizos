<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('category', 100)->nullable();
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->string('cover_image', 255)->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_published')->default(false);
            $table->date('enrollment_start')->nullable();
            $table->date('enrollment_end')->nullable();
            $table->foreignId('created_by')->constrained('employees')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

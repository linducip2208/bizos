<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('code', 50);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('default_days');
            $table->integer('max_days')->nullable();
            $table->boolean('is_annual')->default(true);
            $table->boolean('is_paid')->default(true);
            $table->boolean('require_attachment')->default(false);
            $table->boolean('require_approval')->default(true);
            $table->integer('min_approval_level')->default(1);
            $table->enum('applicable_gender', ['all', 'male', 'female'])->default('all');
            $table->enum('applicable_marital', ['all', 'single', 'married'])->default('all');
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};

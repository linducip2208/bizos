<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thr_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('religious_holiday', 100);
            $table->integer('min_months_service')->default(1);
            $table->enum('formula', ['1x_salary', 'prorated', 'custom']);
            $table->text('custom_formula')->nullable();
            $table->integer('payment_deadline_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thr_configs');
    }
};

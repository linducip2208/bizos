<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpjs_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->enum('bpjs_type', ['tk_jht', 'tk_jp', 'tk_jkk', 'tk_jkm', 'kes']);
            $table->decimal('company_rate', 5, 4);
            $table->decimal('employee_rate', 5, 4);
            $table->decimal('max_salary_cap', 20, 2)->nullable();
            $table->smallInteger('effective_year');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpjs_configs');
    }
};

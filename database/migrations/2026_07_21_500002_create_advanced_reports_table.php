<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advanced_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 30)->default('pivot');
            $table->json('config')->nullable();
            $table->json('data_source')->nullable();
            $table->json('row_fields')->nullable();
            $table->json('column_fields')->nullable();
            $table->json('value_fields')->nullable();
            $table->json('filters')->nullable();
            $table->json('calculated_fields')->nullable();
            $table->json('conditional_formats')->nullable();
            $table->json('chart_config')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_public')->default(false);
            $table->string('embed_token', 100)->nullable()->unique();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advanced_reports');
    }
};

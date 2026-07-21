<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('esg_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('period'); // 2026-Q1, 2026-H1, 2026-annual
            $table->string('period_start');
            $table->string('period_end');
            $table->string('framework'); // gri, pojk_51, ifrs_s1_s2, sasb
            $table->string('status')->default('draft'); // draft, published, archived
            $table->string('file_path')->nullable();
            $table->json('report_data'); // full report JSON
            $table->json('scores')->nullable(); // {total, environmental, social, governance}
            $table->text('executive_summary')->nullable();
            $table->string('prepared_by')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('esg_reports');
    }
};

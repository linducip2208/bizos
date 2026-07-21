<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_breaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('breach_type'); // unauthorized_access, data_leak, malware, physical_theft, insider, third_party
            $table->string('severity'); // low, medium, high, critical
            $table->text('description');
            $table->timestamp('discovered_at');
            $table->timestamp('contained_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('affected_records_count')->default(0);
            $table->json('affected_data_types')->nullable(); // [nama, email, phone, ktp, financial, health]
            $table->text('root_cause')->nullable();
            $table->text('immediate_actions')->nullable();
            $table->text('corrective_actions')->nullable();
            $table->timestamp('notified_dpa_at')->nullable();
            $table->timestamp('notified_subjects_at')->nullable();
            $table->string('dpa_report_number')->nullable();
            $table->string('status')->default('open'); // open, investigating, contained, resolved, closed
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('investigated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_breaches');
    }
};

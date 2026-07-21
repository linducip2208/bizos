<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_audit_findings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iso_audit_id')->constrained('iso_audits')->cascadeOnDelete();
            $table->string('finding_number')->unique();
            $table->enum('classification', ['nc_major', 'nc_minor', 'observation', 'ofi']); // opportunity for improvement
            $table->string('iso_clause')->nullable(); // ISO 27001 clause reference
            $table->text('description');
            $table->text('evidence')->nullable();
            $table->text('corrective_action')->nullable();
            $table->foreignId('responsible_person_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('target_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->enum('status', ['open', 'in_progress', 'closed', 'verified'])->default('open');
            $table->text('verification_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_audit_findings');
    }
};

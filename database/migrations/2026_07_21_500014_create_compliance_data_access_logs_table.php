<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_data_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('accessed_by')->constrained('users')->cascadeOnDelete();
            $table->string('data_subject_type'); // employee, client, supplier, candidate
            $table->unsignedBigInteger('data_subject_id');
            $table->string('purpose');
            $table->string('legal_basis')->nullable(); // consent, contract, legal_obligation, legitimate_interest
            $table->string('access_method'); // view, export, edit, delete
            $table->json('accessed_fields')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['data_subject_type', 'data_subject_id'], 'cdal_subject_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_data_access_logs');
    }
};

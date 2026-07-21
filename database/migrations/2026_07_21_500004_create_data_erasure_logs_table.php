<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_erasure_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('subject_type'); // employee, client
            $table->unsignedBigInteger('subject_id');
            $table->string('requested_by_name')->nullable();
            $table->string('requested_by_email')->nullable();
            $table->string('request_channel'); // email, form, verbal, legal_order
            $table->timestamp('requested_at')->useCurrent();
            $table->string('action'); // full_erasure, anonymization, partial_erasure
            $table->text('reason');
            $table->json('erased_fields')->nullable();
            $table->json('retained_fields')->nullable();
            $table->text('retention_justification')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, processing, completed, rejected
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_erasure_logs');
    }
};

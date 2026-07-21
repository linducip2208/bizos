<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name');
            $table->enum('type', ['contract', 'offer_letter', 'warning_letter', 'certificate', 'invoice_custom', 'custom']);
            $table->longText('content')->nullable();
            $table->json('variables')->nullable();
            $table->string('module')->default('custom');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('document_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('document_templates')->restrictOnDelete();
            $table->string('module');
            $table->unsignedBigInteger('module_id')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->enum('status', ['draft', 'generated', 'signed', 'sent'])->default('draft');
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('signature_provider')->nullable();
            $table->timestamps();
        });

        Schema::create('signature_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name');
            $table->text('api_key_encrypted')->nullable();
            $table->string('base_url')->nullable();
            $table->string('api_format')->default('rest');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_generation_id')->constrained('document_generations')->cascadeOnDelete();
            $table->string('provider');
            $table->string('external_id')->nullable();
            $table->enum('status', ['sent', 'viewed', 'signed', 'completed', 'declined', 'expired'])->default('sent');
            $table->json('signers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_requests');
        Schema::dropIfExists('signature_providers');
        Schema::dropIfExists('document_generations');
        Schema::dropIfExists('document_templates');
    }
};

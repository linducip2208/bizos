<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('contract_number')->unique();
            $table->string('title');
            $table->enum('contract_type', [
                'service',
                'procurement',
                'tenancy',
                'employment',
                'project',
                'subcontractor',
                'partnership',
                'other',
            ]);
            $table->enum('status', [
                'draft',
                'pending_approval',
                'active',
                'amended',
                'expired',
                'terminated',
                'renewed',
            ])->default('draft');
            $table->string('party_type')->nullable()->comment('Polymorphic counterparty type');
            $table->unsignedBigInteger('party_id')->nullable()->comment('Polymorphic counterparty id');
            $table->index(['party_type', 'party_id']);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->decimal('value', 18, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->text('description')->nullable();
            $table->longText('terms_conditions')->nullable();
            $table->json('sla_details')->nullable();
            $table->json('obligations')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('document_templates')->nullOnDelete();
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('parent_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('warranty_registration_id')->constrained('warranty_registrations')->restrictOnDelete();
            $table->string('claim_number', 50);
            $table->date('claim_date');
            $table->text('issue_description');
            $table->text('diagnosis')->nullable();
            $table->text('resolution')->nullable();
            $table->enum('status', ['submitted', 'approved', 'rejected', 'in_progress', 'resolved'])->default('submitted');
            $table->enum('resolution_type', ['repair', 'replace', 'refund'])->nullable();
            $table->decimal('cost', 20, 2)->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index('claim_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('policy_number')->unique();
            $table->string('category'); // access_control, data_classification, incident_response, acceptable_use, etc.
            $table->text('description')->nullable();
            $table->string('version')->default('1.0');
            $table->text('content')->nullable();
            $table->string('document_path')->nullable(); // path to PDF
            $table->date('effective_date');
            $table->date('review_due')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft'); // draft, active, under_review, archived
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_policies');
    }
};

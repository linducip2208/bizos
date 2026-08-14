<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('rfq_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('supplier_category')->nullable();
            $table->dateTime('submission_deadline');
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', [
                'draft',
                'sent',
                'open',
                'closed',
                'awarded',
                'cancelled',
            ])->default('draft');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfqs');
    }
};

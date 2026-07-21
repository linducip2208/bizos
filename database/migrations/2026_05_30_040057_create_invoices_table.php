<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->enum('invoice_type', ['sales', 'purchase', 'credit_note', 'debit_note']);
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('reference_entity', 100);
            $table->unsignedBigInteger('reference_id');
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('discount_amount', 20, 2)->default(0);
            $table->decimal('tax_amount', 20, 2)->default(0);
            $table->decimal('total', 20, 2)->default(0);
            $table->decimal('paid_amount', 20, 2)->default(0);
            $table->decimal('remaining_amount', 20, 2);
            $table->enum('status', ['draft', 'sent', 'partial', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['reference_entity', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

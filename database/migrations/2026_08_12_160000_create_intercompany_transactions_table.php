<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intercompany_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('from_company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('to_company_id')->constrained('companies')->restrictOnDelete();
            $table->enum('transaction_type', ['sale', 'purchase', 'transfer', 'payment', 'expense_allocation']);
            $table->date('transaction_date');
            $table->string('reference_number', 100);
            $table->decimal('amount', 20, 2);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'completed', 'rejected', 'void'])->default('draft');
            $table->foreignId('journal_entry_id_from')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('journal_entry_id_to')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intercompany_transactions');
    }
};

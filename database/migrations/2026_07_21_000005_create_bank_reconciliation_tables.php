<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->string('account_name', 100);
            $table->string('branch', 100)->nullable();
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->decimal('current_balance', 20, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->decimal('closing_balance', 20, 2)->default(0);
            $table->decimal('statement_balance', 20, 2)->default(0);
            $table->decimal('difference', 20, 2)->default(0);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['credit', 'debit']);
            $table->string('description', 255);
            $table->string('reference_number', 100)->nullable();
            $table->decimal('amount', 20, 2);
            $table->boolean('is_reconciled')->default(false);
            $table->foreignId('reconciliation_id')->nullable()->constrained('bank_reconciliations')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained('bank_reconciliations')->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('bank_transaction_id')->nullable()->constrained('bank_transactions')->nullOnDelete();
            $table->decimal('matched_amount', 20, 2)->default(0);
            $table->enum('type', ['matched', 'unmatched_journal', 'unmatched_bank', 'adjustment']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('from_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->date('transfer_date');
            $table->decimal('amount', 20, 2);
            $table->decimal('exchange_rate', 15, 6)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transfers');
        Schema::dropIfExists('reconciliation_items');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('bank_accounts');
    }
};

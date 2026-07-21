<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->enum('type', ['deposit', 'bond', 'mutual_fund', 'stock', 'government_bond', 'corporate_bond', 'money_market', 'other']);
            $table->string('name', 255);
            $table->string('investment_number', 100)->nullable()->unique();
            $table->string('institution', 255)->nullable();
            $table->decimal('principal_amount', 18, 2);
            $table->decimal('current_value', 18, 2)->nullable();
            $table->decimal('interest_rate_percent', 7, 4)->default(0);
            $table->enum('interest_type', ['fixed', 'floating', 'zero_coupon', 'dividend'])->default('fixed');
            $table->string('interest_payment_frequency', 50)->nullable();
            $table->date('start_date');
            $table->date('maturity_date')->nullable();
            $table->date('next_interest_date')->nullable();
            $table->decimal('total_accrued_interest', 18, 2)->default(0);
            $table->decimal('total_interest_earned', 18, 2)->default(0);
            $table->enum('status', ['active', 'matured', 'liquidated', 'impaired'])->default('active');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('investment_id')->constrained('investments')->cascadeOnDelete();
            $table->enum('type', ['purchase', 'interest_income', 'dividend', 'redemption', 'partial_redemption', 'fee', 'impairment', 'revaluation']);
            $table->date('transaction_date');
            $table->decimal('amount', 18, 2);
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->decimal('exchange_rate', 10, 6)->default(1);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bank_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('bank_name', 255);
            $table->enum('facility_type', ['overdraft', 'term_loan', 'revolving_credit', 'lc', 'bank_guarantee', 'factoring', 'supply_chain_finance', 'other']);
            $table->string('facility_number', 100)->nullable()->unique();
            $table->string('name', 255);
            $table->decimal('limit_amount', 18, 2);
            $table->decimal('utilized_amount', 18, 2)->default(0);
            $table->decimal('available_amount', 18, 2)->default(0);
            $table->decimal('interest_rate_percent', 7, 4)->default(0);
            $table->decimal('commitment_fee_percent', 7, 4)->nullable();
            $table->date('start_date');
            $table->date('expiry_date');
            $table->date('review_date')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled', 'suspended'])->default('active');
            $table->boolean('is_secured')->default(false);
            $table->text('collateral_description')->nullable();
            $table->decimal('collateral_value', 18, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bank_facility_drawdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('bank_facility_id')->constrained('bank_facilities')->cascadeOnDelete();
            $table->date('drawdown_date');
            $table->date('repayment_date')->nullable();
            $table->decimal('amount', 18, 2);
            $table->decimal('interest_rate_percent', 7, 4);
            $table->decimal('outstanding_amount', 18, 2)->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->enum('status', ['outstanding', 'repaid', 'rolled_over', 'defaulted'])->default('outstanding');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bank_facility_covenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('bank_facility_id')->constrained('bank_facilities')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('metric', 255);
            $table->string('requirement', 255);
            $table->string('actual_value', 255)->nullable();
            $table->boolean('is_compliant')->nullable();
            $table->date('last_tested_at')->nullable();
            $table->date('next_test_date')->nullable();
            $table->enum('frequency', ['monthly', 'quarterly', 'semi_annual', 'annual'])->default('quarterly');
            $table->enum('status', ['compliant', 'breach', 'waiver_granted', 'pending_test'])->default('pending_test');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('forex_rates_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->decimal('buy_rate', 12, 6);
            $table->decimal('sell_rate', 12, 6);
            $table->decimal('mid_rate', 12, 6)->nullable();
            $table->string('source', 100)->nullable();
            $table->timestamps();

            $table->unique(['currency_id', 'snapshot_date']);
            $table->index('snapshot_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forex_rates_snapshots');
        Schema::dropIfExists('bank_facility_covenants');
        Schema::dropIfExists('bank_facility_drawdowns');
        Schema::dropIfExists('bank_facilities');
        Schema::dropIfExists('investment_transactions');
        Schema::dropIfExists('investments');
    }
};

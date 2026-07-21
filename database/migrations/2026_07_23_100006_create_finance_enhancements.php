<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('closing_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('year', 4);
            $table->string('month', 2);
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'year', 'month']);
        });

        Schema::create('opening_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('coa_id')->constrained('coa')->restrictOnDelete();
            $table->string('period_year', 4);
            $table->string('period_month', 2);
            $table->decimal('debit_amount', 20, 2)->default(0);
            $table->decimal('credit_amount', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'coa_id', 'period_year', 'period_month']);
        });

        Schema::create('trial_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('period_year', 4);
            $table->string('period_month', 2);
            $table->foreignId('coa_id')->constrained('coa')->restrictOnDelete();
            $table->decimal('opening_debit', 20, 2)->default(0);
            $table->decimal('opening_credit', 20, 2)->default(0);
            $table->decimal('movement_debit', 20, 2)->default(0);
            $table->decimal('movement_credit', 20, 2)->default(0);
            $table->decimal('closing_debit', 20, 2)->default(0);
            $table->decimal('closing_credit', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'coa_id', 'period_year', 'period_month']);
        });

        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('profit_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('financial_consolidations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('child_company_id')->constrained('companies')->restrictOnDelete();
            $table->enum('consolidation_type', ['balance_sheet', 'income_statement']);
            $table->string('period_year', 4);
            $table->string('period_month', 2);
            $table->json('mapping_config')->nullable();
            $table->enum('status', ['draft', 'processed', 'final'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_consolidations');
        Schema::dropIfExists('profit_centers');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('trial_balances');
        Schema::dropIfExists('opening_balances');
        Schema::dropIfExists('closing_periods');
    }
};

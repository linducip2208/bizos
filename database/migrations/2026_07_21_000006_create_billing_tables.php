<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 15, 2);
            $table->decimal('yearly_price', 15, 2);
            $table->integer('max_users')->default(5);
            $table->integer('max_companies')->default(1);
            $table->integer('max_branches')->default(1);
            $table->json('features')->nullable();
            $table->enum('tier', ['standard', 'gold', 'platinum'])->default('standard');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->enum('status', ['trial', 'active', 'grace', 'expired', 'cancelled'])->default('trial');
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });

        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->date('due_date');
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('due_date');
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('subscription_invoices')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_id')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->string('proof_path')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('subscription_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->string('metric');
            $table->decimal('usage_count', 15, 4)->default(0);
            $table->date('recorded_at');
            $table->timestamps();

            $table->index(['company_id', 'metric']);
            $table->unique(['company_id', 'subscription_id', 'metric', 'recorded_at'], 'subscription_usage_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_usage');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscription_invoices');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};

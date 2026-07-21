<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('property_type', 30)->default('house');
            $table->string('unit_number', 50);
            $table->string('building_name', 200)->nullable();
            $table->integer('floor')->nullable();
            $table->decimal('land_area_sqm', 10, 2)->nullable();
            $table->decimal('building_area_sqm', 10, 2)->nullable();
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->text('address')->nullable();
            $table->string('ownership_certificate', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('current_market_value', 15, 2)->nullable();
            $table->string('status', 20)->default('available');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tenancy_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('property_unit_id')->constrained('property_units')->restrictOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('contract_number', 50);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('monthly_rent', 15, 2)->default(0);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->decimal('service_charge_monthly', 15, 2)->default(0);
            $table->decimal('sinking_fund_monthly', 15, 2)->default(0);
            $table->integer('payment_due_day')->default(1);
            $table->decimal('late_fee_percent', 5, 2)->default(5);
            $table->boolean('renewal_option')->default(false);
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('service_charge_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('property_unit_id')->constrained('property_units')->restrictOnDelete();
            $table->foreignId('tenancy_contract_id')->constrained('tenancy_contracts')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('invoice_number', 50);
            $table->decimal('rent_amount', 15, 2)->default(0);
            $table->decimal('service_charge', 15, 2)->default(0);
            $table->decimal('sinking_fund', 15, 2)->default(0);
            $table->decimal('electricity', 15, 2)->default(0);
            $table->decimal('water', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->date('due_date');
            $table->string('status', 20)->default('unpaid');
            $table->timestamps();
        });

        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('property_unit_id')->constrained('property_units')->restrictOnDelete();
            $table->foreignId('tenancy_contract_id')->nullable()->constrained('tenancy_contracts')->nullOnDelete();
            $table->string('requested_by', 200);
            $table->string('category', 30)->default('other');
            $table->text('description');
            $table->string('priority', 20)->default('medium');
            $table->string('status', 20)->default('reported');
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
        Schema::dropIfExists('service_charge_invoices');
        Schema::dropIfExists('tenancy_contracts');
        Schema::dropIfExists('property_units');
    }
};

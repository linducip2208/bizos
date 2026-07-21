<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('rab_items')->nullOnDelete();
            $table->string('item_code', 50);
            $table->string('description');
            $table->string('unit', 30);
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('category', 30)->default('material');
            $table->decimal('weight_percent', 5, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('progress_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('billing_number', 50);
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->decimal('physical_progress_percent', 5, 2)->default(0);
            $table->decimal('previous_claimed_percent', 5, 2)->default(0);
            $table->decimal('current_claimed_percent', 5, 2)->default(0);
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('retention_percent', 5, 2)->default(5);
            $table->decimal('retention_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_site_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('quantity_on_site', 15, 4)->default(0);
            $table->decimal('quantity_used', 15, 4)->default(0);
            $table->date('last_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'project_id', 'product_id'], 'psi_project_product_unique');
        });

        Schema::create('daily_site_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->date('report_date');
            $table->string('weather', 20)->nullable();
            $table->decimal('temperature', 5, 1)->nullable();
            $table->integer('worker_count')->default(0);
            $table->json('heavy_equipment_used')->nullable();
            $table->json('materials_used')->nullable();
            $table->text('work_description')->nullable();
            $table->string('progress_photo_path')->nullable();
            $table->text('issues')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('subcontractor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('project_id')->constrained('projects')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->string('contract_number', 50);
            $table->text('scope_of_work');
            $table->decimal('contract_amount', 15, 2)->default(0);
            $table->decimal('retention_percent', 5, 2)->default(5);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontractor_contracts');
        Schema::dropIfExists('daily_site_reports');
        Schema::dropIfExists('project_site_inventories');
        Schema::dropIfExists('progress_billings');
        Schema::dropIfExists('rab_items');
    }
};

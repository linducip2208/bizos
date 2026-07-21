<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bill of Materials
        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->comment('Finished good');
            $table->string('name');
            $table->string('revision')->default('1.0');
            $table->decimal('quantity', 12, 4)->default(1)->comment('Output qty per batch');
            $table->string('unit')->default('pcs');
            $table->boolean('is_active')->default(true);
            $table->date('effective_date')->nullable();
            $table->date('obsolete_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        // BOM Items (raw materials / components)
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('bill_of_materials')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete()->comment('Raw material / component');
            $table->decimal('quantity_per_unit', 12, 4);
            $table->string('unit')->default('pcs');
            $table->decimal('scrap_percent', 5, 2)->default(0);
            $table->boolean('is_critical')->default(false);
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Work Centers
        Schema::create('work_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->enum('type', ['machine', 'manual', 'assembly'])->default('manual');
            $table->decimal('capacity_per_day', 10, 2)->comment('Capacity per day');
            $table->string('capacity_uom')->default('unit')->comment('unit/hour/shift');
            $table->decimal('hourly_cost', 10, 2)->default(0);
            $table->decimal('overhead_rate_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Routing Operations
        Schema::create('routing_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('bom_id')->nullable()->constrained('bill_of_materials')->nullOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->string('operation_name');
            $table->integer('sequence');
            $table->decimal('setup_time_minutes', 8, 2)->default(0);
            $table->decimal('run_time_minutes_per_unit', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Production Orders
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('po_number')->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('bom_id')->nullable()->constrained('bill_of_materials')->nullOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained('work_centers')->nullOnDelete();
            $table->decimal('planned_quantity', 12, 4);
            $table->decimal('produced_quantity', 12, 4)->default(0);
            $table->decimal('rejected_quantity', 12, 4)->default(0);
            $table->datetime('planned_start')->nullable();
            $table->datetime('planned_end')->nullable();
            $table->datetime('actual_start')->nullable();
            $table->datetime('actual_end')->nullable();
            $table->enum('status', ['draft', 'planned', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
        });

        // Production Order Materials
        Schema::create('production_order_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('bom_item_id')->nullable()->constrained('bom_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('required_quantity', 12, 4);
            $table->decimal('issued_quantity', 12, 4)->default(0);
            $table->decimal('returned_quantity', 12, 4)->default(0);
            $table->enum('status', ['pending', 'issued', 'partial', 'complete'])->default('pending');
            $table->timestamps();
        });

        // Production Order Operations
        Schema::create('production_order_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('routing_operation_id')->nullable()->constrained('routing_operations')->nullOnDelete();
            $table->foreignId('work_center_id')->constrained('work_centers')->restrictOnDelete();
            $table->datetime('planned_start')->nullable();
            $table->datetime('planned_end')->nullable();
            $table->datetime('actual_start')->nullable();
            $table->datetime('actual_end')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Production QC Checks
        Schema::create('production_qc_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->enum('check_type', ['incoming_material', 'in_process', 'final'])->default('final');
            $table->string('parameter');
            $table->text('specification')->nullable();
            $table->enum('result', ['pass', 'fail', 'conditional'])->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->datetime('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Waste Logs
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained('production_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->decimal('quantity', 12, 4);
            $table->string('unit')->default('pcs');
            $table->enum('waste_type', ['scrap', 'rework', 'reject'])->default('scrap');
            $table->text('reason')->nullable();
            $table->decimal('cost_impact', 12, 2)->default(0);
            $table->foreignId('reported_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('created_at')->nullable();
        });

        // Subcontract Orders
        Schema::create('subcontract_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity_sent', 12, 4)->default(0);
            $table->decimal('quantity_received', 12, 4)->default(0);
            $table->decimal('quantity_rejected', 12, 4)->default(0);
            $table->date('sent_date')->nullable();
            $table->date('expected_return')->nullable();
            $table->date('actual_return')->nullable();
            $table->enum('status', ['draft', 'sent', 'in_progress', 'received', 'completed'])->default('draft');
            $table->decimal('cost', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontract_orders');
        Schema::dropIfExists('waste_logs');
        Schema::dropIfExists('production_qc_checks');
        Schema::dropIfExists('production_order_operations');
        Schema::dropIfExists('production_order_materials');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('routing_operations');
        Schema::dropIfExists('work_centers');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('bill_of_materials');
    }
};

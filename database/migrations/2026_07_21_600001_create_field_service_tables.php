<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->string('contract_number', 50)->unique();
            $table->enum('contract_type', ['maintenance_regular', 'maintenance_comprehensive', 'installation', 'repair']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'annually']);
            $table->decimal('billing_amount', 20, 2)->default(0);
            $table->enum('service_frequency', ['weekly', 'biweekly', 'monthly', 'quarterly']);
            $table->integer('equipment_count')->default(0);
            $table->enum('status', ['draft', 'active', 'suspended', 'expired', 'terminated'])->default('draft');
            $table->integer('sla_response_hours')->default(4);
            $table->integer('sla_resolution_hours')->default(24);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('contracted_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_contract_id')->constrained('service_contracts')->cascadeOnDelete();
            $table->string('equipment_name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_date')->nullable();
            $table->enum('status', ['active', 'under_repair', 'decommissioned'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('service_contract_id')->nullable()->constrained('service_contracts')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('equipment_id')->nullable()->constrained('contracted_equipment')->nullOnDelete();
            $table->string('wo_number', 30)->unique();
            $table->enum('service_type', ['preventive', 'corrective', 'emergency', 'installation', 'inspection']);
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('description');
            $table->string('reported_by')->nullable();
            $table->dateTime('scheduled_start')->nullable();
            $table->dateTime('scheduled_end')->nullable();
            $table->dateTime('actual_start')->nullable();
            $table->dateTime('actual_end')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('helper_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('status', ['open', 'assigned', 'en_route', 'in_progress', 'completed', 'verified', 'cancelled'])->default('open');
            $table->text('resolution')->nullable();
            $table->string('customer_signature_path')->nullable();
            $table->string('photo_before_path')->nullable();
            $table->string('photo_after_path')->nullable();
            $table->decimal('gps_checkin_lat', 10, 7)->nullable();
            $table->decimal('gps_checkin_lng', 10, 7)->nullable();
            $table->decimal('gps_checkout_lat', 10, 7)->nullable();
            $table->decimal('gps_checkout_lng', 10, 7)->nullable();
            $table->decimal('travel_distance_km', 8, 2)->nullable()->default(0);
            $table->decimal('labor_hours', 8, 2)->nullable()->default(0);
            $table->decimal('parts_cost', 20, 2)->default(0);
            $table->decimal('service_charge', 20, 2)->default(0);
            $table->decimal('total_cost', 20, 2)->default(0);
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->tinyInteger('customer_rating')->nullable()->unsigned();
            $table->text('customer_feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('work_order_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 20, 2);
            $table->decimal('subtotal', 20, 2);
            $table->boolean('from_van_stock')->default(false);
            $table->timestamps();
        });

        Schema::create('technician_vans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technician_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('license_plate', 20)->nullable();
            $table->decimal('current_location_lat', 10, 7)->nullable();
            $table->decimal('current_location_lng', 10, 7)->nullable();
            $table->dateTime('last_location_update')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('van_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('van_id')->constrained('technician_vans')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('min_quantity', 12, 3)->default(0);
            $table->decimal('reorder_point', 12, 3)->default(0);
            $table->date('last_restock_date')->nullable();
            $table->timestamps();
        });

        Schema::create('service_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->enum('service_type', ['preventive', 'corrective', 'installation', 'inspection']);
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('service_checklists')->cascadeOnDelete();
            $table->string('description');
            $table->boolean('is_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('work_order_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('service_checklist_items')->cascadeOnDelete();
            $table->boolean('is_checked')->default(false);
            $table->dateTime('checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_checklist_items');
        Schema::dropIfExists('service_checklist_items');
        Schema::dropIfExists('service_checklists');
        Schema::dropIfExists('van_inventories');
        Schema::dropIfExists('technician_vans');
        Schema::dropIfExists('work_order_parts');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('contracted_equipment');
        Schema::dropIfExists('service_contracts');
    }
};

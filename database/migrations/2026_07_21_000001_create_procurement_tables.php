<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 200);
            $table->string('address', 500)->nullable();
            $table->string('pic_name', 200)->nullable();
            $table->string('pic_phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('contact_person', 200)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('tax_number', 50)->nullable();
            $table->string('payment_terms', 50)->nullable()->default('NET30');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('pr_number', 50)->unique();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('employees')->restrictOnDelete();
            $table->date('date_required')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'converted'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_requisition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name', 300);
            $table->string('specification', 500)->nullable();
            $table->string('unit', 50);
            $table->decimal('quantity', 15, 4);
            $table->decimal('estimated_price', 20, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('po_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('pr_id')->nullable()->constrained('purchase_requisitions')->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('tax_amount', 20, 2)->default(0);
            $table->decimal('discount_amount', 20, 2)->default(0);
            $table->decimal('shipping_cost', 20, 2)->default(0);
            $table->decimal('total', 20, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->enum('status', ['draft', 'sent', 'approved', 'partially_received', 'received', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('employees')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('pr_item_id')->nullable()->constrained('purchase_requisition_items')->nullOnDelete();
            $table->string('item_name', 300);
            $table->string('specification', 500)->nullable();
            $table->string('unit', 50);
            $table->decimal('quantity', 15, 4);
            $table->decimal('received_qty', 15, 4)->default(0);
            $table->decimal('unit_price', 20, 2);
            $table->decimal('tax_rate', 5, 2)->default(11);
            $table->boolean('is_taxable')->default(true);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('subtotal', 20, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('grn_number', 50)->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('received_by')->constrained('employees')->restrictOnDelete();
            $table->date('receipt_date');
            $table->string('delivery_note', 100)->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('draft');
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('po_item_id')->constrained('purchase_order_items')->restrictOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name', 300);
            $table->string('unit', 50);
            $table->decimal('quantity_received', 15, 4);
            $table->decimal('quantity_accepted', 15, 4);
            $table->decimal('quantity_rejected', 15, 4)->default(0);
            $table->decimal('unit_price', 20, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
        Schema::dropIfExists('goods_receipts');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_requisition_items');
        Schema::dropIfExists('purchase_requisitions');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('warehouses');
    }
};

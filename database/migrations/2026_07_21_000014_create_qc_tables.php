<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('category', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('goods_receipt_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
            $table->foreignId('grn_item_id')->constrained('goods_receipt_items')->cascadeOnDelete();
            $table->foreignId('quality_check_id')->constrained('quality_checks')->restrictOnDelete();
            $table->enum('result', ['pass', 'fail', 'pending'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('inspected_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('inspected_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_requisition_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_requisition_id')->constrained('purchase_requisitions')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->decimal('quoted_price', 20, 2);
            $table->integer('delivery_days')->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_requisition_quotes');
        Schema::dropIfExists('goods_receipt_inspections');
        Schema::dropIfExists('quality_checks');
    }
};

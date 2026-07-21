<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('movement_type', 50);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity_in', 15, 4)->default(0);
            $table->decimal('quantity_out', 15, 4)->default(0);
            $table->decimal('unit_cost', 20, 2)->nullable();
            $table->decimal('running_quantity', 15, 4);
            $table->decimal('running_cost', 20, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('employees')->restrictOnDelete();
            $table->timestamp('movement_date');
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('movement_date');
        });

        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->decimal('quantity', 15, 4)->default(0);
            $table->decimal('average_cost', 20, 2)->default(0);
            $table->decimal('last_cost', 20, 2)->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id', 'warehouse_id'], 'stock_balance_unique');
        });

        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('opname_number', 50)->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->date('opname_date');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'adjusted', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('employees')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->decimal('system_quantity', 15, 4);
            $table->decimal('physical_quantity', 15, 4);
            $table->decimal('difference', 15, 4);
            $table->decimal('unit_cost', 20, 2)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
        Schema::dropIfExists('stock_opnames');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('stock_movements');
    }
};

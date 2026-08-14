<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('three_way_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->restrictOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('goods_receipts')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->enum('match_status', ['pending', 'matched', 'partial_match', 'mismatch'])->default('pending');
            $table->decimal('po_total', 20, 2)->default(0);
            $table->decimal('gr_total', 20, 2)->nullable();
            $table->decimal('invoice_total', 20, 2)->nullable();
            $table->boolean('quantity_match')->default(false);
            $table->boolean('price_match')->default(false);
            $table->boolean('total_match')->default(false);
            $table->decimal('variance_amount', 20, 2)->nullable();
            $table->decimal('variance_percent', 8, 4)->nullable();
            $table->json('mismatch_details')->nullable();
            $table->enum('resolution_status', ['open', 'accepted', 'rejected', 'resolved'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('matched_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('three_way_matches');
    }
};

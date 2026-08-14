<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_hold_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('name', 100)->nullable();
            $table->json('items');
            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('discount_total', 20, 2)->default(0);
            $table->decimal('tax_total', 20, 2)->default(0);
            $table->decimal('grand_total', 20, 2)->default(0);
            $table->enum('status', ['open', 'recalled'])->default('open');
            $table->timestamp('held_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_hold_orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->date('shift_date');
            $table->timestamp('opening_time');
            $table->decimal('opening_balance', 20, 2)->default(0);
            $table->timestamp('closing_time')->nullable();
            $table->decimal('closing_balance', 20, 2)->nullable();
            $table->decimal('expected_cash', 20, 2)->nullable();
            $table->decimal('actual_cash', 20, 2)->nullable();
            $table->decimal('difference', 20, 2)->nullable();
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_sales', 20, 2)->default(0);
            $table->enum('status', ['open', 'closed', 'reconciled'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('tax_config_id')->constrained('tax_configs')->restrictOnDelete();
            $table->string('reference_type', 100);
            $table->unsignedBigInteger('reference_id');
            $table->decimal('base_amount', 20, 2);
            $table->decimal('tax_amount', 20, 2);
            $table->date('tax_date');
            $table->enum('payment_status', ['unpaid', 'paid', 'deferred'])->default('unpaid');
            $table->date('paid_date')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_transactions');
    }
};
